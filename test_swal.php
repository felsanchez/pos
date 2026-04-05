<?php
session_start();
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
    die("No session");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Swal</title>
    <script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="vistas/plugins/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>
    <button onclick="testIcon()">Test with icon</button>
    <button onclick="testType()">Test with type</button>
    <script>
        function testIcon() {
            swal({
                icon: 'success',
                title: 'Test with icon',
                showConfirmButton: true
            });
        }
        function testType() {
            swal({
                type: 'success',
                title: 'Test with type',
                showConfirmButton: true
            });
        }
    </script>
</body>
</html>
