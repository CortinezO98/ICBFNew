<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";
    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\IOFactory;

    $array_meses=[1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre"];
	// error_reporting(E_ALL);
	// ini_set('display_errors', '1');

    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_segmento=validar_input(base64_decode($_GET['seg']));
    $fecha_calculadora=validar_input($_GET['date']);
    
    $titulo_reporte="Gestión Calidad-Calculadora Muestral ".date('Y-m-d H_i_s').".xlsx";

    $consulta_string="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_usuario`, `cmm_monitor`, `cmm_muestra_auditoria`, TL.`usu_nombres_apellidos`, TU.`usu_nombres_apellidos`, TS.`cms_nombre_segmento`, TR.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_muestras` LEFT JOIN `tb_administrador_usuario` AS TL ON `tb_gestion_calidad_cmuestral_muestras`.`cmm_monitor`=TL.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_muestras`.`cmm_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_calidad_cmuestral_segmento` AS TS ON `tb_gestion_calidad_cmuestral_muestras`.`cmm_segmento`=TS.`cms_id` LEFT JOIN `tb_administrador_usuario` AS TR ON TU.`usu_supervisor`=TR.`usu_id` WHERE `cmm_mes`=? AND `cmm_segmento`=?";
    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param('ss', $fecha_calculadora, $id_segmento);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    // Creamos nueva instancia de PHPExcel 
    $spreadsheet = new Spreadsheet();

    // Establecer propiedades
    $spreadsheet->getProperties()
    ->setCreator("GIS Gestión Integrada de Servicios")
    ->setLastModifiedBy($_SESSION['usu_nombre_completo'])
    ->setTitle("GIS Gestión Integrada de Servicios")
    ->setSubject("GIS Gestión Integrada de Servicios")
    ->setDescription("GIS Gestión Integrada de Servicios")
    ->setKeywords("GIS Gestión Integrada de Servicios")
    ->setCategory("Reporte");

    //Estilos para todo el libro de excel
        $spreadsheet->getDefaultStyle()->getfont()->setName("Calibri");
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $styleArrayTitulos = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => '2E2E2E')
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap'=>true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => '72BF44',
                ]
            ],
        ];
        
    //Activar hoja 0
    $sheet = $spreadsheet->getActiveSheet(0);
    
    // Nombramos la hoja 0
    $spreadsheet->getActiveSheet()->setTitle('Reporte');

    //Estilos de la Hoja 0
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $spreadsheet->getActiveSheet()->getStyle('A3:G3')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A3:G3');
        $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);

    // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A3','Doc Identidad');
        $spreadsheet->getActiveSheet()->setCellValue('B3','Nombres y Apellidos');
        $spreadsheet->getActiveSheet()->setCellValue('C3','Segmento');
        $spreadsheet->getActiveSheet()->setCellValue('D3','Responsable');
        $spreadsheet->getActiveSheet()->setCellValue('E3','Líder de Calidad');
        $spreadsheet->getActiveSheet()->setCellValue('F3','Muestra Aleatoria');
        $spreadsheet->getActiveSheet()->setCellValue('G3','Fecha');
    
    //Ingresar Data consultada a partir de la fila 4

    for ($i=4; $i < count($resultado_registros)+4; $i++) {
        $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-4][4]);
        $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-4][8]);
        $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-4][9]);
        $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-4][10]);
        $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$resultado_registros[$i-4][7]);
        $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$resultado_registros[$i-4][6]);
        $spreadsheet->getActiveSheet()->setCellValue('G'.$i,$resultado_registros[$i-4][2]);
    }

    //Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$titulo_reporte.'"');
    header('Cache-Control: max-age=0');

    // Guardamos el archivo, en este caso lo guarda con el mismo nombre del php
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
?>