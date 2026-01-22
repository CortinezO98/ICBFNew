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
    //array columnas
        $array_columnas[]="A";
        $array_columnas[]="B";
        $array_columnas[]="C";
        $array_columnas[]="D";
        $array_columnas[]="E";
        $array_columnas[]="F";
        $array_columnas[]="G";
        $array_columnas[]="H";
        $array_columnas[]="I";
        $array_columnas[]="J";
        $array_columnas[]="K";
        $array_columnas[]="L";
        $array_columnas[]="M";
        $array_columnas[]="N";
        $array_columnas[]="O";
        $array_columnas[]="P";
        $array_columnas[]="Q";
        $array_columnas[]="R";
        $array_columnas[]="S";
        $array_columnas[]="T";
        $array_columnas[]="U";
        $array_columnas[]="V";
        $array_columnas[]="W";
        $array_columnas[]="X";
        $array_columnas[]="Y";
        $array_columnas[]="Z";
        $array_columnas[]="AA";
        $array_columnas[]="AB";
        $array_columnas[]="AC";
        $array_columnas[]="AD";
        $array_columnas[]="AE";
        $array_columnas[]="AF";
        $array_columnas[]="AG";
        $array_columnas[]="AH";
        $array_columnas[]="AI";
        $array_columnas[]="AJ";
        $array_columnas[]="AK";
        $array_columnas[]="AL";
        $array_columnas[]="AM";
        $array_columnas[]="AN";
        $array_columnas[]="AO";
        $array_columnas[]="AP";
        $array_columnas[]="AQ";
        $array_columnas[]="AR";
        $array_columnas[]="AS";
        $array_columnas[]="AT";
        $array_columnas[]="AU";
        $array_columnas[]="AV";
        $array_columnas[]="AW";
        $array_columnas[]="AX";
        $array_columnas[]="AY";
        $array_columnas[]="AZ";
        $array_columnas[]="BA";
        $array_columnas[]="BB";
        $array_columnas[]="BC";
        $array_columnas[]="BD";
        $array_columnas[]="BE";
        $array_columnas[]="BF";
        $array_columnas[]="BG";
        $array_columnas[]="BH";
        $array_columnas[]="BI";
        $array_columnas[]="BJ";
        $array_columnas[]="BK";
        $array_columnas[]="BL";
        $array_columnas[]="BM";
        $array_columnas[]="BN";
        $array_columnas[]="BO";
        $array_columnas[]="BP";
        $array_columnas[]="BQ";
        $array_columnas[]="BR";
        $array_columnas[]="BS";
        $array_columnas[]="BT";
        $array_columnas[]="BU";
        $array_columnas[]="BV";
        $array_columnas[]="BW";
        $array_columnas[]="BX";
        $array_columnas[]="BY";
        $array_columnas[]="BZ";
        $array_columnas[]="CA";
        $array_columnas[]="CB";
        $array_columnas[]="CC";
        $array_columnas[]="CD";
        $array_columnas[]="CE";
        $array_columnas[]="CF";
        $array_columnas[]="CG";
        $array_columnas[]="CH";
        $array_columnas[]="CI";
        $array_columnas[]="CJ";
        $array_columnas[]="CK";
        $array_columnas[]="CL";
        $array_columnas[]="CM";
        $array_columnas[]="CN";
        $array_columnas[]="CO";
        $array_columnas[]="CP";
        $array_columnas[]="CQ";
        $array_columnas[]="CR";
        $array_columnas[]="CS";
        $array_columnas[]="CT";
        $array_columnas[]="CU";
        $array_columnas[]="CV";
        $array_columnas[]="CW";
        $array_columnas[]="CX";
        $array_columnas[]="CY";
        $array_columnas[]="CZ";
    //array columnas    

    $id_registro=validar_input(base64_decode($_GET['reg']));
    $fecha_calculadora=validar_input($_GET['date']);
    
    $titulo_reporte="Gestión Calidad-Calculadora Muestral ".date('Y-m-d H_i_s').".xlsx";

    $consulta_string_segmento="SELECT `cms_id`, `cms_calculadora`, `cms_nombre_segmento`, `cms_peso` FROM `tb_gestion_calidad_cmuestral_segmento` WHERE `cms_calculadora`=? ORDER BY `cms_nombre_segmento` ASC";

    $consulta_registros_segmento = $enlace_db->prepare($consulta_string_segmento);
    $consulta_registros_segmento->bind_param("s", $id_registro);
    $consulta_registros_segmento->execute();
    $resultado_registros_segmento = $consulta_registros_segmento->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_malla="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_usuario_segmento`, `ccmm_coordinador`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_monitoreo_lunes`, `ccmm_monitoreo_lunes`, `ccmm_monitoreo_lunes`, `ccmm_actualiza_usuario`, `ccmm_actualiza_fecha`, `ccmm_registro_usuario`, `ccmm_registro_fecha`, TU.`usu_nombres_apellidos`, TC.`usu_nombres_apellidos`, TTL.`gmtl_turno`, TTL.`gmtl_color_convencion`, TTM.`gmtl_turno`, TTM.`gmtl_color_convencion`, TTX.`gmtl_turno`, TTX.`gmtl_color_convencion`, TTJ.`gmtl_turno`, TTJ.`gmtl_color_convencion`, TTV.`gmtl_turno`, TTV.`gmtl_color_convencion`, TTS.`gmtl_turno`, TTS.`gmtl_color_convencion`, TTD.`gmtl_turno`, TTD.`gmtl_color_convencion`, TCAM.`ac_nombre_campania`, `ccmm_monitor`, TMONITOR.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_malla` LEFT JOIN `tb_gestion_calidad_cmuestral_segmento` AS TSEG ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_segmento`=TSEG.`cms_id` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TC ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_coordinador`=TC.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TMONITOR ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_monitor`=TMONITOR.`usu_id` LEFT JOIN `tb_gestion_calidad_cmuestral_malla_turnos_lista` AS TTL ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_lunes`=TTL.`gmtl_id` LEFT JOIN `tb_gestion_calidad_cmuestral_malla_turnos_lista` AS TTM ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_martes`=TTM.`gmtl_id` LEFT JOIN `tb_gestion_calidad_cmuestral_malla_turnos_lista` AS TTX ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_miercoles`=TTX.`gmtl_id` LEFT JOIN `tb_gestion_calidad_cmuestral_malla_turnos_lista` AS TTJ ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_jueves`=TTJ.`gmtl_id` LEFT JOIN `tb_gestion_calidad_cmuestral_malla_turnos_lista` AS TTV ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_viernes`=TTV.`gmtl_id` LEFT JOIN `tb_gestion_calidad_cmuestral_malla_turnos_lista` AS TTS ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_sabado`=TTS.`gmtl_id` LEFT JOIN `tb_gestion_calidad_cmuestral_malla_turnos_lista` AS TTD ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_domingo`=TTD.`gmtl_id` LEFT JOIN `tb_administrador_campania` AS TCAM ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario_segmento`=TCAM.`ac_id` WHERE `ccmm_mes`=? AND TSEG.`cms_calculadora`=? ORDER BY TU.`usu_nombres_apellidos` ASC";
    $consulta_registros_malla = $enlace_db->prepare($consulta_string_malla);
    $consulta_registros_malla->bind_param('ss', $fecha_calculadora, $id_registro);
    $consulta_registros_malla->execute();
    $resultado_registros_malla = $consulta_registros_malla->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_malla); $i++) {
        $turno_seg_usuario[$resultado_registros_malla[$i][2]][]=$resultado_registros_malla[$i][3];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['id']=$resultado_registros_malla[$i][0];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['nombre']=$resultado_registros_malla[$i][20];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['coordinador']=$resultado_registros_malla[$i][21];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['segmento']=$resultado_registros_malla[$i][36];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['muestra']=$resultado_registros_malla[$i][13];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['monitor']=$resultado_registros_malla[$i][38];
        
        $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]=$resultado_registros_malla[$i][22];
        $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]=$resultado_registros_malla[$i][24];
        $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]=$resultado_registros_malla[$i][26];
        $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]=$resultado_registros_malla[$i][28];
        $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]=$resultado_registros_malla[$i][30];
        $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]=$resultado_registros_malla[$i][32];
        $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]=$resultado_registros_malla[$i][34];
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
    

    for ($k=0; $k < count($resultado_registros_segmento); $k++) { 
       
        $spreadsheet->createSheet($k);
        //Activar hoja 0
        $sheet = $spreadsheet->setActiveSheetIndex($k);
        
        // Nombramos la hoja 0
        $spreadsheet->getActiveSheet()->setTitle($resultado_registros_segmento[$k][2]);

        //Estilos de la Hoja 0
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('4')->setRowHeight(20);
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
        
        $spreadsheet->getActiveSheet()->getStyle('A4:M4')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A4:M4');

        // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A4','Id Usuario');
        $spreadsheet->getActiveSheet()->setCellValue('B4','Nombres y Apellidos');
        $spreadsheet->getActiveSheet()->setCellValue('C4','Segmento');
        $spreadsheet->getActiveSheet()->setCellValue('D4','Coordinador');
        $spreadsheet->getActiveSheet()->setCellValue('E4','Monitor');
        $spreadsheet->getActiveSheet()->setCellValue('F4','Lunes');
        $spreadsheet->getActiveSheet()->setCellValue('G4','Martes');
        $spreadsheet->getActiveSheet()->setCellValue('H4','Miércoles');
        $spreadsheet->getActiveSheet()->setCellValue('I4','Jueves');
        $spreadsheet->getActiveSheet()->setCellValue('J4','Viernes');
        $spreadsheet->getActiveSheet()->setCellValue('K4','Sábado');
        $spreadsheet->getActiveSheet()->setCellValue('L4','Domingo');
        $spreadsheet->getActiveSheet()->setCellValue('M4','Muestra Aleatoria');
        
        $spreadsheet->getActiveSheet()->setCellValue('A1','Segmento: '.$resultado_registros_segmento[$k][2]);
        $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha Muestreo: '.$fecha_calculadora);
        $spreadsheet->getActiveSheet()->setCellValue('A3','Fecha reporte: '.date('Y-m-d H:i:s'));

        //Ingresar Data consultada a partir de la fila 4

        for ($i=5; $i < count($turno_seg_usuario[$resultado_registros_segmento[$k][0]])+5; $i++) {
            $id_usuario_item=$turno_seg_usuario[$resultado_registros_segmento[$k][0]][$i-5];
            
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$id_usuario_item);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$turno_mostrar_datos[$id_usuario_item]['nombre']);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$turno_mostrar_datos[$id_usuario_item]['segmento']);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$turno_mostrar_datos[$id_usuario_item]['coordinador']);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$turno_mostrar_datos[$id_usuario_item]['monitor']);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$turno_mostrar[$resultado_registros_segmento[$k][0]][$id_usuario_item][0]);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i,$turno_mostrar[$resultado_registros_segmento[$k][0]][$id_usuario_item][1]);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i,$turno_mostrar[$resultado_registros_segmento[$k][0]][$id_usuario_item][2]);
            $spreadsheet->getActiveSheet()->setCellValue('I'.$i,$turno_mostrar[$resultado_registros_segmento[$k][0]][$id_usuario_item][3]);
            $spreadsheet->getActiveSheet()->setCellValue('J'.$i,$turno_mostrar[$resultado_registros_segmento[$k][0]][$id_usuario_item][4]);
            $spreadsheet->getActiveSheet()->setCellValue('K'.$i,$turno_mostrar[$resultado_registros_segmento[$k][0]][$id_usuario_item][5]);
            $spreadsheet->getActiveSheet()->setCellValue('L'.$i,$turno_mostrar[$resultado_registros_segmento[$k][0]][$id_usuario_item][6]);
            $spreadsheet->getActiveSheet()->setCellValue('M'.$i,str_replace('<br>',"\n", $turno_mostrar_datos[$id_usuario_item]['muestra']));
            
        }
    }

    // echo "<pre>";
    // print_r($turno_seg_usuario);
    // print_r($turno_mostrar_datos);
    // print_r($turno_mostrar);

    //Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$titulo_reporte.'"');
    header('Cache-Control: max-age=0');

    // Guardamos el archivo, en este caso lo guarda con el mismo nombre del php
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
?>