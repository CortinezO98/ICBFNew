<?php
    require_once("../config/validaciones_seguridad_raiz.php");
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
        header("Location:index.php");
    }
    require_once("../config/conexion_db.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\IOFactory;
    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');
    $array_meses=[1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre"];

    if(isset($_POST["exportar_reporte"])){
        $tipo_reporte=trim($_POST['tipo_reporte']);
        $fecha_inicio=trim($_POST['fecha_inicio']);
        $fecha_fin=trim($_POST['fecha_fin']);

        $titulo_reporte="Gestión Turnos - ".$tipo_reporte." ".date('d-m-Y H_i_s').".xlsx";

        if ($tipo_reporte=="Consolidado") {
            $consulta_string="SELECT `cot_id`, `cot_usuario`, `cot_turno_inicio`, `cot_turno_fin`, `cot_turno_duracion`, `cot_bk1_inicio`, `cot_bk1_fin`, `cot_bk1_duracion`, `cot_bk2_inicio`, `cot_bk2_fin`, `cot_bk2_duracion`, `cot_almuerzo_inicio`, `cot_almuerzo_fin`, `cot_almuerzo_duracion`, `cot_fuente`, `cot_fuente_nombre`, `cot_observaciones_inicio_turno`, `cot_logueo_inicial`, `cot_pk`, TU.`usu_nombres_apellidos`, TPI.`ap_nombre_piloto` FROM `tb_control_turno` LEFT JOIN `tb_administrador_usuario`AS TU ON `tb_control_turno`.`cot_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TPI ON TU.`usu_piloto`=TPI.`ap_id` WHERE `cot_turno_inicio`>='".$fecha_inicio."' AND `cot_turno_fin`<='".$fecha_fin." 23:59:59' ORDER BY `cot_turno_inicio` ASC";
            $consulta_registros = $enlace_db->prepare($consulta_string);
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
        }
    }

    // Creamos nueva instancia de PHPExcel 
    $spreadsheet = new Spreadsheet();

    // Establecer propiedades
    $spreadsheet->getProperties()
    ->setCreator("IQ-DIAN Gestión Integrada de Servicios")
    ->setLastModifiedBy($_SESSION['usu_nombre_completo'])
    ->setTitle("IQ-DIAN Gestión Integrada de Servicios")
    ->setSubject("IQ-DIAN Gestión Integrada de Servicios")
    ->setDescription("IQ-DIAN Gestión Integrada de Servicios")
    ->setKeywords("IQ-DIAN Gestión Integrada de Servicios")
    ->setCategory("Reporte");

    //Estilos para todo el libro de excel
        $spreadsheet->getDefaultStyle()->getfont()->setName("Calibri");
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $styleArrayTitulos = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
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
                    'argb' => '262944',
                ]
            ],
        ];

        $styleArrayTitulos_2 = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
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
                    'argb' => '76933C',
                ]
            ],
        ];

        $styleArrayTitulos_3 = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
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
                    'argb' => '0F243E',
                ]
            ],
        ];
        
        $styleArrayTitulos_4 = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
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
                    'argb' => '00B050',
                ]
            ],
        ];

        $styleArrayContenido = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap'=>true,
            ],
        ];
        
    //Activar hoja 0
    $sheet = $spreadsheet->getActiveSheet(0);
    
    // Nombramos la hoja 0
    $spreadsheet->getActiveSheet()->setTitle('Reporte Gestión Turnos');

    if ($tipo_reporte=="Consolidado") {
        //Estilos de la Hoja 0
            $spreadsheet->getActiveSheet()->getRowDimension('5')->setRowHeight(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            
            $spreadsheet->getActiveSheet()->getStyle('A5:F5')->applyFromArray($styleArrayTitulos);
            $spreadsheet->getActiveSheet()->setAutoFilter('A5:F5');
            $spreadsheet->getActiveSheet()->getStyle('5')->getAlignment()->setWrapText(true);

        // Escribiendo los titulos
            $spreadsheet->getActiveSheet()->setCellValue('A5','Doc. Usuario');
            $spreadsheet->getActiveSheet()->setCellValue('B5','Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('C5','Piloto');
            $spreadsheet->getActiveSheet()->setCellValue('D5','Inicio Turno');
            $spreadsheet->getActiveSheet()->setCellValue('E5','Fin Turno');
            $spreadsheet->getActiveSheet()->setCellValue('F5','Observaciones');
            
            $spreadsheet->getActiveSheet()->setCellValue('A1','Tipo reporte: '.$tipo_reporte);
            $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha reporte: '.date('Y-m-d H:i:s'));

        //Ingresar Data consultada a partir de la fila 6

        for ($i=6; $i < count($resultado_registros)+6; $i++) {
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-6][1]);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-6][19]);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-6][20]);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-6][2]);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$resultado_registros[$i-6][3]);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$resultado_registros[$i-6][16]);
        }
    }

    //Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$titulo_reporte.'"');
    header('Cache-Control: max-age=0');

    // Guardamos el archivo, en este caso lo guarda con el mismo nombre del php
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
?>