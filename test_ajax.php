<?php
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
$_POST['search'] = ['value' => ''];
$_POST['order'] = [['column' => 0, 'dir' => 'asc']];
$_POST['columns'] = [['data' => 'id']];

require_once 'ajax/datatable-productos.ajax.php';
