<?php

// Cargar variables de entorno

require_once __DIR__ . '/../config.php';

class Conexion{ 

	static private $conexion = null;

	static public function conectar(){ 

		if (self::$conexion !== null) {
			return self::$conexion;
		}

		try {
			// Obtener credenciales desde variables de entorno
			// Si no existen las variables, la conexión fallará

			$host = env('DB_HOST');
			$dbname = env('DB_NAME'); // Por defecto es el de las variables de entorno
			$user = env('DB_USER');
			$pass = env('DB_PASS'); 

			if (!$host || !$dbname || !$user) {
				$error = 'Las variables de entorno de la base de datos no están configuradas. Revisa el archivo .env';

				Logger::error($error, [
					'host' => $host ? 'configurado' : 'faltante',
					'dbname' => $dbname ? 'configurado' : 'faltante',
					'user' => $user ? 'configurado' : 'faltante'
				]);
				throw new Exception($error);
			}

			// Detección dinámica del subdominio de inquilino (Multi-Tenant)
			$httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

			// Solo procesamos si no está vacío, no es localhost y no es la IP local de desarrollo
			if (!empty($httpHost) && $httpHost !== 'localhost' && $httpHost !== '127.0.0.1') {
				$partes = explode('.', $httpHost);

				// Buscamos que sea un subdominio (ej: cliente.kontrolpos.com tiene al menos 3 partes)
				// Excluimos 'www' por si acceden con www.tudominio.com
				if (count($partes) >= 3 && $partes[0] !== 'www') {
					// Limpiar subdominio para evitar inyecciones
					$subdominio = preg_replace('/[^a-zA-Z0-9_-]/', '', $partes[0]);

					// Base de datos Master (configurable en .env, por defecto toma el valor de DB_NAME)
					$masterDbname = env('DB_MASTER_NAME', env('DB_NAME'));

					// Conectar a la base de datos Master para buscar las credenciales del cliente
					$pdoMaster = new PDO("mysql:host={$host};dbname={$masterDbname}", $user, $pass);
					$pdoMaster->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$pdoMaster->exec("set names utf8");

					$stmt = $pdoMaster->prepare("SELECT db_name, db_user, db_pass, db_host, estado FROM clientes_tenants WHERE subdominio = :subdominio LIMIT 1");
					$stmt->execute([':subdominio' => $subdominio]);
					$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

					if (!$tenant) {
						header('HTTP/1.1 403 Forbidden');
						die("Error: El subdominio '" . htmlspecialchars($subdominio) . "' no está registrado en Kontrol POS.");
					}

					if ($tenant['estado'] !== 'activo') {
						header('HTTP/1.1 403 Forbidden');
						die("Error: El acceso para el subdominio '" . htmlspecialchars($subdominio) . "' se encuentra suspendido.");
					}

					// Si todo está bien, cargamos los datos de conexión del inquilino
					$host = $tenant['db_host'];
					$dbname = $tenant['db_name'];
					$user = $tenant['db_user'];
					$pass = $tenant['db_pass'];
				}
			}

 			$link = new PDO("mysql:host={$host};dbname={$dbname}",
							$user,
							$pass); 

			$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$link->exec("set names utf8");
 
			// Log de conexión exitosa comentado para evitar ruido en logs
			// Solo se registran errores de conexión

			 //Logger::info('Conexión a base de datos establecida correctamente', [
			 //	'database' => $dbname
			 // ]);

			self::$conexion = $link;
 			return self::$conexion;
 
		} catch (PDOException $e) {
			Logger::error('Error al conectar a la base de datos', [

				'exception' => $e,
				'database' => $dbname ?? 'desconocida'
			]);

			throw $e;
		}
	}

	static public function conectarMaster(){
		try {
			$host = env('DB_HOST');
			$dbname = env('DB_MASTER_NAME', env('DB_NAME'));
			$user = env('DB_USER');
			$pass = env('DB_PASS');

			$link = new PDO("mysql:host={$host};dbname={$dbname}", $user, $pass);
			$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$link->exec("set names utf8");
			return $link;
		} catch (PDOException $e) {
			Logger::error('Error al conectar a la base de datos Master', [
				'exception' => $e,
				'database' => $dbname ?? 'desconocida'
			]);
			throw $e;
		}
	}
}

