<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";
    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\IOFactory;

    $array_meses=[1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre"];

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
    if(isset($_POST["exportar_reporte"])){
        $data_consulta=array();
        $tipo_reporte=trim($_POST['tipo_reporte']);
        $estado=trim($_POST['estado']);
        $fecha_inicio=trim($_POST['fecha_inicio']);
        $fecha_fin=trim($_POST['fecha_fin']);

        if ($estado=="Todos") {
            $filtro_estado="";
        } else {
            $filtro_estado=" AND `ga_estado`=?";
            array_push($data_consulta, $estado);
        }

        array_push($data_consulta, $fecha_inicio);
        
        if ($tipo_reporte=="Consolidado") {
            $titulo_reporte="Gestión Alertas-Consolidado ".date('Y-m-d H_i_s').".xlsx";
            $titulo_hoja="Consolidado";
            $consulta_string="SELECT `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`, `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`, `ga_fecha_cierre`, `ga_accion_mejora`, `ga_revisa_n1_usuario`, `ga_revisa_n1_fecha`, `ga_revisa_n2_usuario`, `ga_revisa_n2_fecha`, `ga_registro_usuario`, `ga_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos`, `ga_fecha_cierre`, `ga_observaciones_n1`, `ga_observaciones_n2` FROM `tb_gestion_alertas` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id` WHERE 1=1 ".$filtro_estado." AND `ga_fecha_alerta`>=? AND `ga_fecha_alerta`<='".$fecha_fin." 23:59:59' ORDER BY `ga_id` ASC";
            
        }

        $consulta_registros = $enlace_db->prepare($consulta_string);
        $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
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
    
    // // Nombramos la hoja 0
    $spreadsheet->getActiveSheet()->setTitle($titulo_hoja);

    if ($tipo_reporte=="Consolidado") {
        // //Estilos de la Hoja 0
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('4')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('5')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('6')->setRowHeight(20);
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
        $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(20);
        
        $spreadsheet->getActiveSheet()->getStyle('A6:V6')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A6:V6');

        // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A6','NÚMERO DE ALERTA');
        $spreadsheet->getActiveSheet()->setCellValue('B6','ESTADO');
        $spreadsheet->getActiveSheet()->setCellValue('C6','SIM ASOCIADO');
        $spreadsheet->getActiveSheet()->setCellValue('D6','SIM QUEJA O RECLAMO AL MOMENTO DE LA ENCUESTA');
        $spreadsheet->getActiveSheet()->setCellValue('E6','FECHA DE ALERTA');
        $spreadsheet->getActiveSheet()->setCellValue('F6','FECHA DE ATENCIÓN AL CIUDADANO');
        $spreadsheet->getActiveSheet()->setCellValue('G6','REGIONAL AFECTADA POR LA ALERTA');
        $spreadsheet->getActiveSheet()->setCellValue('H6','PUNTO DE ATENCIÓN AFECTADO POR LA ALERTA (CENTRO ZONAL)');
        $spreadsheet->getActiveSheet()->setCellValue('I6','CATEGORÍA DE AFECTACIÓN');
        $spreadsheet->getActiveSheet()->setCellValue('J6','DESCRIPCIÓN DE LA ALERTA');
        $spreadsheet->getActiveSheet()->setCellValue('K6','VALIDACIÓN DE LA AFECTACIÓN DE LA LÍNEA TÉCNICA DEL PROCESO (SI / NO)');
        $spreadsheet->getActiveSheet()->setCellValue('L6','FECHA DE REMISIÓN AL AGENTE ESPECIALIZADO ');
        $spreadsheet->getActiveSheet()->setCellValue('M6','OBSERVACIONES NIVEL 2 ');
        $spreadsheet->getActiveSheet()->setCellValue('N6','FECHA DE REQUERIMIENTO A LA REGIONAL');
        $spreadsheet->getActiveSheet()->setCellValue('O6','RESPUESTA POR PARTE DE LA REGIONAL AL REQUERIMIENTO (SI / NO)');
        $spreadsheet->getActiveSheet()->setCellValue('P6','FECHA RESPUESTA DE LA REGIONAL');
        $spreadsheet->getActiveSheet()->setCellValue('Q6','SÍNTESIS DE LA RESPUESTA DE LA REGIONAL Y CONCEPTO TÉCNICO FRENTE A LA RESPUESTA DE LA REGIONAL');
        $spreadsheet->getActiveSheet()->setCellValue('R6','DECISIÓN DEL CASO');
        $spreadsheet->getActiveSheet()->setCellValue('S6','No. SNC/AC');
        $spreadsheet->getActiveSheet()->setCellValue('T6','SEGUIMIENTO A LA DECISIÓN ');
        $spreadsheet->getActiveSheet()->setCellValue('U6','CIERRE DE LA ALERTA (SI/NO)');
        $spreadsheet->getActiveSheet()->setCellValue('V6','AGENTE REMITENTE');
        
        $spreadsheet->getActiveSheet()->setCellValue('A1','Gestión Alertas');
        $spreadsheet->getActiveSheet()->setCellValue('A2','Tipo reporte: '.$tipo_reporte);
        $spreadsheet->getActiveSheet()->setCellValue('A3','Estado: '.$estado);
        $spreadsheet->getActiveSheet()->setCellValue('A4','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);
        $spreadsheet->getActiveSheet()->setCellValue('A5','Fecha reporte: '.date('Y-m-d H:i:s'));

        //Ingresar Data consultada a partir de la fila 2

        for ($i=7; $i < count($resultado_registros)+7; $i++) {
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-7][0]);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-7][3]);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-7][1]);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-7][2]);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$resultado_registros[$i-7][4]);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$resultado_registros[$i-7][5]);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i,$resultado_registros[$i-7][28]);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i,$resultado_registros[$i-7][29]);
            $spreadsheet->getActiveSheet()->setCellValue('I'.$i,$resultado_registros[$i-7][11]);
            $spreadsheet->getActiveSheet()->setCellValue('J'.$i,$resultado_registros[$i-7][12]);
            $spreadsheet->getActiveSheet()->setCellValue('K'.$i,$resultado_registros[$i-7][13]);
            $spreadsheet->getActiveSheet()->setCellValue('L'.$i,$resultado_registros[$i-7][14]);
            $spreadsheet->getActiveSheet()->setCellValue('M'.$i,$resultado_registros[$i-7][33]);
            $spreadsheet->getActiveSheet()->setCellValue('N'.$i,$resultado_registros[$i-7][15]);
            $spreadsheet->getActiveSheet()->setCellValue('O'.$i,$resultado_registros[$i-7][16]);
            $spreadsheet->getActiveSheet()->setCellValue('P'.$i,$resultado_registros[$i-7][16]);
            $spreadsheet->getActiveSheet()->setCellValue('Q'.$i,'');
            $spreadsheet->getActiveSheet()->setCellValue('R'.$i,$resultado_registros[$i-7][18]);
            $spreadsheet->getActiveSheet()->setCellValue('S'.$i,$resultado_registros[$i-7][19]);
            $spreadsheet->getActiveSheet()->setCellValue('T'.$i,'');
            $spreadsheet->getActiveSheet()->setCellValue('U'.$i,$resultado_registros[$i-7][20]);
            $spreadsheet->getActiveSheet()->setCellValue('V'.$i,$resultado_registros[$i-7][30]);
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