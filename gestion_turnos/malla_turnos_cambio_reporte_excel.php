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
        $campania=validar_input($_POST['campania']);
        $estado=validar_input($_POST['estado']);
        $fecha_inicio=validar_input($_POST['fecha_inicio']);
        $fecha_fin=validar_input($_POST['fecha_fin']).' 23:59:59';
        $titulo_reporte="Gestión Turnos-Cambio Turnos-".date('Y-m-d H_i_s').".xlsx";
        
        // Inicializa variable tipo array
        $data_consulta=array();
        array_push($data_consulta, $fecha_inicio);
        array_push($data_consulta, $fecha_fin);

        $filtro_campania="";
        if ($campania!="Todas") {
            $filtro_campania=" AND TUO.`usu_campania`=?";
            array_push($data_consulta, $campania);
        }

        $filtro_estado="";
        if ($estado!="Todos") {
            $filtro_estado=" AND `cmtct_estado`=?";
            array_push($data_consulta, $estado);
        }

        $consulta_string="SELECT `cmtct_id`, `cmtct_usuario_origen`, TUO.`usu_nombres_apellidos`, TCUO.`ac_nombre_campania`, `cmtct_uo_turno_anterior_inicio`, `cmtct_uo_turno_anterior_fin`, `cmtct_uo_turno_nuevo_inicio`, `cmtct_uo_turno_nuevo_fin`, `cmtct_usuario_destino`, TUD.`usu_nombres_apellidos`, TCUD.`ac_nombre_campania`, `cmtct_ud_turno_anterior_inicio`, `cmtct_ud_turno_anterior_fin`, `cmtct_ud_turno_nuevo_inicio`, `cmtct_ud_turno_nuevo_fin`, `cmtct_responsable`, TR.`usu_nombres_apellidos`, `cmtct_estado`, `cmtct_observaciones`, `cmtct_registro_fecha` FROM `tb_control_turno_malla_cambio_turno` LEFT JOIN `tb_administrador_usuario` AS TUO ON `tb_control_turno_malla_cambio_turno`.`cmtct_usuario_origen`=TUO.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUD ON `tb_control_turno_malla_cambio_turno`.`cmtct_usuario_destino`=TUD.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TR ON `tb_control_turno_malla_cambio_turno`.`cmtct_responsable`=TR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TCUO ON TUO.`usu_campania`=TCUO.`ac_id` LEFT JOIN `tb_administrador_campania` AS TCUD ON TUD.`usu_campania`=TCUD.`ac_id` WHERE `cmtct_registro_fecha`>=? AND `cmtct_registro_fecha`<=? ".$filtro_estado." ".$filtro_campania." ORDER BY `cmtct_uo_turno_anterior_inicio`";

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
    $spreadsheet->getActiveSheet()->setTitle('Reporte Cambio Turno');

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
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $spreadsheet->getActiveSheet()->getStyle('A3:N3')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A3:N3');
        $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);

    // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A3','Estado');
        $spreadsheet->getActiveSheet()->setCellValue('B3','Fecha');
        $spreadsheet->getActiveSheet()->setCellValue('C3','Doc. Solicitante');
        $spreadsheet->getActiveSheet()->setCellValue('D3','Solicitante');
        $spreadsheet->getActiveSheet()->setCellValue('E3','Campaña');
        $spreadsheet->getActiveSheet()->setCellValue('F3','Turno Anterior');
        $spreadsheet->getActiveSheet()->setCellValue('G3','Turno Nuevo');
        $spreadsheet->getActiveSheet()->setCellValue('H3','Doc. Solicitado');
        $spreadsheet->getActiveSheet()->setCellValue('I3','Solicitado');
        $spreadsheet->getActiveSheet()->setCellValue('J3','Turno Anterior');
        $spreadsheet->getActiveSheet()->setCellValue('K3','Turno Nuevo');
        $spreadsheet->getActiveSheet()->setCellValue('L3','Responsable');
        $spreadsheet->getActiveSheet()->setCellValue('M3','Observaciones');
        $spreadsheet->getActiveSheet()->setCellValue('N3','Fecha Registro');

        $spreadsheet->getActiveSheet()->setCellValue('A1','Tipo reporte: Cambio Turno');
        $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);

    //Ingresar Data consultada a partir de la fila 4
    for ($i=4; $i < count($resultado_registros)+4; $i++) {
        $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-4][17]);
        $spreadsheet->getActiveSheet()->setCellValue('B'.$i,date('Y-m-d', strtotime($resultado_registros[$i-4][4])));
        $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-4][1]);
        $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-4][2]);
        $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$resultado_registros[$i-4][3]);
        $spreadsheet->getActiveSheet()->setCellValue('F'.$i,date('H:i', strtotime($resultado_registros[$i-4][4])).'-'.date('H:i', strtotime($resultado_registros[$i-4][5])));
        $spreadsheet->getActiveSheet()->setCellValue('G'.$i,date('H:i', strtotime($resultado_registros[$i-4][6])).'-'.date('H:i', strtotime($resultado_registros[$i-4][7])));
        $spreadsheet->getActiveSheet()->setCellValue('H'.$i,$resultado_registros[$i-4][8]);
        $spreadsheet->getActiveSheet()->setCellValue('I'.$i,$resultado_registros[$i-4][9]);
        $spreadsheet->getActiveSheet()->setCellValue('J'.$i,date('H:i', strtotime($resultado_registros[$i-4][11])).'-'.date('H:i', strtotime($resultado_registros[$i-4][12])));
        $spreadsheet->getActiveSheet()->setCellValue('K'.$i,date('H:i', strtotime($resultado_registros[$i-4][13])).'-'.date('H:i', strtotime($resultado_registros[$i-4][14])));
        $spreadsheet->getActiveSheet()->setCellValue('L'.$i,$resultado_registros[$i-4][16]);
        $spreadsheet->getActiveSheet()->setCellValue('M'.$i,$resultado_registros[$i-4][18]);
        $spreadsheet->getActiveSheet()->setCellValue('N'.$i,$resultado_registros[$i-4][19]);
    }

    //Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$titulo_reporte.'"');
    header('Cache-Control: max-age=0');

    // Guardamos el archivo, en este caso lo guarda con el mismo nombre del php
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
?>