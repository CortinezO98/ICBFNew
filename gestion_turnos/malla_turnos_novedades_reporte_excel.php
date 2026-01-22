<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos-Malla";
    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\IOFactory;

// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    if(isset($_POST["exportar_reporte"])){
        $tipo_reporte=validar_input($_POST['tipo_reporte']);
        $campania=validar_input($_POST['campania']);
        $fecha_inicio=validar_input($_POST['fecha_inicio']);
        $fecha_fin=validar_input($_POST['fecha_fin']).' 23:59:59';
        $titulo_reporte="Gestión Turnos-".$tipo_reporte." ".date('Y-m-d H_i_s').".xlsx";
        
        // Inicializa variable tipo array
        $data_consulta=array();
        array_push($data_consulta, $fecha_inicio);
        array_push($data_consulta, $fecha_fin);

        $filtro_campania="";
        if ($campania!="Todas") {
            $filtro_campania=" AND TUT.`usu_campania`=?";
            array_push($data_consulta, $campania);
        }

        $consulta_string="SELECT `ctmn_id`, `ctm_usuario`, TUT.`usu_nombres_apellidos`, `ctmn_tipo`, `ctmn_fecha_inicio`, `ctmn_fecha_fin`, `ctmn_registro_usuario`, TUR.`usu_nombres_apellidos`, `ctmn_registro_fecha`, `ctmn_observaciones`, TC.`ac_nombre_campania`, TR.`usu_nombres_apellidos` FROM `tb_control_turno_malla_novedades` LEFT JOIN `tb_administrador_usuario` AS TUT ON `tb_control_turno_malla_novedades`.`ctm_usuario`=TUT.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON `tb_control_turno_malla_novedades`.`ctmn_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TUT.`usu_campania`=TC.`ac_id` LEFT JOIN `tb_administrador_usuario` AS TR ON TUT.`usu_supervisor`=TR.`usu_id` WHERE `ctmn_registro_fecha`>=? AND `ctmn_registro_fecha`<=? ".$filtro_campania." ORDER BY `ctmn_registro_fecha` DESC";

        $consulta_registros = $enlace_db->prepare($consulta_string);
        if (count($data_consulta)>0) {
            // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
            $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        }
        $consulta_registros->execute();
        $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
    }

    // Creamos nueva instancia de PHPExcel 
    $spreadsheet = new Spreadsheet();

    // Establecer propiedades
    $spreadsheet->getProperties()
    ->setCreator("IQ-ICBF Gestión Integrada de Servicios")
    ->setLastModifiedBy($_SESSION['usu_nombre_completo'])
    ->setTitle("IQ-ICBF Gestión Integrada de Servicios")
    ->setSubject("IQ-ICBF Gestión Integrada de Servicios")
    ->setDescription("IQ-ICBF Gestión Integrada de Servicios")
    ->setKeywords("IQ-ICBF Gestión Integrada de Servicios")
    ->setCategory("Reporte");

    //Estilos para todo el libro de excel
        $spreadsheet->getDefaultStyle()->getfont()->setName("Calibri");
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        //Estilos para todo el libro de excel
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
    $spreadsheet->getActiveSheet()->setTitle('Novedades');

    //Estilos de la Hoja 0
        $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(80);
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $spreadsheet->getActiveSheet()->getStyle('A3:I3')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A3:I3');
        $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);

    // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A3','Fecha');
        $spreadsheet->getActiveSheet()->setCellValue('B3','Doc. Usuario');
        $spreadsheet->getActiveSheet()->setCellValue('C3','Nombres y Apellidos');
        $spreadsheet->getActiveSheet()->setCellValue('D3','Segmento');
        $spreadsheet->getActiveSheet()->setCellValue('E3','Responsable');
        $spreadsheet->getActiveSheet()->setCellValue('F3','Novedad');
        $spreadsheet->getActiveSheet()->setCellValue('G3','Observaciones');
        $spreadsheet->getActiveSheet()->setCellValue('H3','Usuario Registro');
        $spreadsheet->getActiveSheet()->setCellValue('I3','Fecha Registro');

        $spreadsheet->getActiveSheet()->setCellValue('A1','Tipo reporte: '.$tipo_reporte);
        $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);

    //Ingresar Data consultada a partir de la fila 4
    $control_fila=4;
    for ($i=4; $i < count($resultado_registros)+4; $i++) {
        $fecha_inicio_control=$resultado_registros[$i-4][4];
        $fecha_fin_control=$resultado_registros[$i-4][5];
        
        $spreadsheet->getActiveSheet()->setCellValue('A'.$control_fila,$fecha_inicio_control);
        $spreadsheet->getActiveSheet()->setCellValue('B'.$control_fila,$resultado_registros[$i-4][1]);
        $spreadsheet->getActiveSheet()->setCellValue('C'.$control_fila,$resultado_registros[$i-4][2]);
        $spreadsheet->getActiveSheet()->setCellValue('D'.$control_fila,$resultado_registros[$i-4][10]);
        $spreadsheet->getActiveSheet()->setCellValue('E'.$control_fila,$resultado_registros[$i-4][11]);
        $spreadsheet->getActiveSheet()->setCellValue('F'.$control_fila,$array_convenciones[$resultado_registros[$i-4][3]]);
        $spreadsheet->getActiveSheet()->setCellValue('G'.$control_fila,$resultado_registros[$i-4][9]);
        $spreadsheet->getActiveSheet()->setCellValue('H'.$control_fila,$resultado_registros[$i-4][7]);
        $spreadsheet->getActiveSheet()->setCellValue('I'.$control_fila,$resultado_registros[$i-4][8]);

        while ($fecha_inicio_control<$fecha_fin_control) {
            $control_fila++;
            $fecha_inicio_control = date("Y-m-d", strtotime("+ 1 day", strtotime($fecha_inicio_control)));
            $spreadsheet->getActiveSheet()->setCellValue('A'.$control_fila,$fecha_inicio_control);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$control_fila,$resultado_registros[$i-4][1]);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$control_fila,$resultado_registros[$i-4][2]);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$control_fila,$resultado_registros[$i-4][10]);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$control_fila,$resultado_registros[$i-4][11]);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$control_fila,$array_convenciones[$resultado_registros[$i-4][3]]);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$control_fila,$resultado_registros[$i-4][9]);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$control_fila,$resultado_registros[$i-4][7]);
            $spreadsheet->getActiveSheet()->setCellValue('I'.$control_fila,$resultado_registros[$i-4][8]);
        }
        $control_fila++;
    }

    //Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$titulo_reporte.'"');
    header('Cache-Control: max-age=0');

    // Guardamos el archivo, en este caso lo guarda con el mismo nombre del php
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
?>