<?php
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
$_POST['search'] = ['value' => '1902'];
$_POST['order'] = [['column' => 0, 'dir' => 'asc']];
$_POST['columns'] = [['data' => 'id'], ['data' => 'codigo']];

require_once 'datatable-productos.ajax.php';
