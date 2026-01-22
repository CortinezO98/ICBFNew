<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";
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
        $id_campania=trim($_POST['id_campania']);
        $tipo_reporte=trim($_POST['tipo_reporte']);
        $estado=trim($_POST['estado']);
        $fecha_inicio=trim($_POST['fecha_inicio']);
        $fecha_fin=trim($_POST['fecha_fin']);

        array_push($data_consulta, $id_campania);

        $consulta_string_campania="SELECT `gem_id`, `gem_nombre_encuesta` FROM `tb_gestion_encuestas_matriz` WHERE `gem_id`=?";
        $consulta_registros_campania = $enlace_db->prepare($consulta_string_campania);
        $consulta_registros_campania->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_campania->execute();
        $resultado_registros_campania = $consulta_registros_campania->get_result()->fetch_all(MYSQLI_NUM);
        
        if ($estado=="Todos") {
            $filtro_estado="";
        } else {
            $filtro_estado=" AND `gera_estado_gestion`=?";
            array_push($data_consulta, $estado);
        }

        array_push($data_consulta, $fecha_inicio);
        
        if ($tipo_reporte=="Radicados") {
            $titulo_reporte="Gestión Encuestas-Radicados ".date('Y-m-d H_i_s').".xlsx";
            $titulo_hoja="Radicados";
            $consulta_string="SELECT `gera_radicado`, `gera_matriz_id`, TE.`gem_nombre_encuesta`, `gera_fecha_peticion`, `gera_peticionario_id`, `gera_peticionario_nombre`, `gera_tel_fijo`, `gera_tel_movil`, `gera_genero`, `gera_motivo`, `gera_regional`, `gera_centro_zonal`, `gera_estado_gestion`, `gera_intentos`, `gera_contactado`, `gera_efectivo`, `gera_ultima_observacion`, `gera_fecha_actualizacion`, `gera_usuario_gestion`, TU.`usu_nombres_apellidos`, `gera_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, `gera_auxiliar1`, `gera_auxiliar2`, `gera_auxiliar3`, `gera_edad` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` LEFT JOIN `tb_gestion_encuestas_matriz` AS TE ON `tb_gestion_encuesta_radicado`.`gera_matriz_id`=TE.`gem_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_encuesta_radicado`.`gera_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_encuesta_radicado`.`gera_centro_zonal`=TCZ.`gercz_id` WHERE `gera_matriz_id`=? ".$filtro_estado." AND `gera_registro_fecha`>=? AND `gera_registro_fecha`<='".$fecha_fin." 23:59:59' ORDER BY `gera_radicado` ASC";
            
        } elseif ($tipo_reporte=="Encuestas") {
            $titulo_reporte="Gestión Encuestas-Encuestas ".date('Y-m-d H_i_s').".xlsx";
            $titulo_hoja="Encuestas";

            $consulta_string_matriz="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, TSEC.`gems_nombre_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_tipo_campo`, `gemp_seccion_segun_respuesta`, `gemp_seccion_complemento`, `gemp_orden_mostrar` FROM `tb_gestion_encuestas_matriz_seccion_pregunta`  LEFT JOIN `tb_gestion_encuestas_matriz_seccion` AS TSEC ON `tb_gestion_encuestas_matriz_seccion_pregunta`.`gemp_seccion`=TSEC.`gems_id` WHERE `gemp_encuesta`=? ORDER BY TSEC.`gems_orden_mostrar` ASC, `gemp_orden_mostrar` ASC";

            $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
            $consulta_registros_matriz->bind_param('s', $id_campania);
            $consulta_registros_matriz->execute();
            $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

            for ($i=0; $i < count($resultado_registros_matriz); $i++) {
                $array_matriz_seccion_id[]=$resultado_registros_matriz[$i][2];
                $array_matriz_seccion[$resultado_registros_matriz[$i][2]]['nombre']=$resultado_registros_matriz[$i][3];
                $array_matriz_seccion[$resultado_registros_matriz[$i][2]]['conteo']+=1;
                $array_matriz_seccion[$resultado_registros_matriz[$i][2]]['preguntas'][]=$resultado_registros_matriz[$i][0];
                $array_matriz_pregunta[$resultado_registros_matriz[$i][0]]=$resultado_registros_matriz[$i][4];

                $array_matriz[$resultado_registros_matriz[$i][2]][$resultado_registros_matriz[$i][0]]="";
            }

            $array_matriz_seccion_id=array_values(array_unique($array_matriz_seccion_id));

            $consulta_string="SELECT `ger_consecutivo`, TM.`gem_nombre_encuesta`, `ger_radicado`, `ger_contactado`, `ger_efectivo`, `ger_observaciones`, `ger_registro_usuario`, TU.`usu_nombres_apellidos`, `ger_registro_fecha`, TR.`gera_registro_fecha`, TD.`gerd_seccion`, TD.`gerd_pregunta`, TD.`gerd_respuesta`, TPR.`gemp_tipo`, TOP.`gemo_opcion_nombre` FROM `tb_gestion_encuesta_registro` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro`.`ger_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON `tb_gestion_encuesta_registro`.`ger_encuesta_id`=TM.`gem_id` LEFT JOIN `tb_gestion_encuesta_radicado` AS TR ON `tb_gestion_encuesta_registro`.`ger_radicado`=TR.`gera_radicado` LEFT JOIN `tb_gestion_encuesta_registro_data` AS TD ON `tb_gestion_encuesta_registro`.`ger_consecutivo`=TD.`gerd_encuesta` LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta` AS TPR ON TD.`gerd_pregunta`=TPR.`gemp_id` LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` AS TOP ON TD.`gerd_respuesta`=TOP.`gemo_id` WHERE `ger_encuesta_id`=? ".$filtro_estado." AND `ger_registro_fecha`>=? AND `ger_registro_fecha`<='".$fecha_fin." 23:59:59' ORDER BY `ger_consecutivo` ASC";


        } elseif ($tipo_reporte=="Control Cambios") {
            $titulo_reporte="Gestión Encuestas-Control Cambios ".date('Y-m-d H_i_s').".xlsx";
            $titulo_hoja="Control Cambios";
            $consulta_string="SELECT `gerlog_id`, `gerlog_radicado`, TM.`gem_nombre_encuesta`, `gerlog_campo`, `gerlog_anterior`, `gerlog_nuevo`, `gerlog_registro_usuario`, TU.`usu_nombres_apellidos`, `gerlog_registro_fecha`, TR.`gera_registro_fecha` FROM `tb_gestion_encuesta_radicado_log` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado_log`.`gerlog_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuesta_radicado` AS TR ON `tb_gestion_encuesta_radicado_log`.`gerlog_radicado`=TR.`gera_radicado` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON TR.`gera_matriz_id`=TM.`gem_id` WHERE TR.`gera_matriz_id`=? ".$filtro_estado." AND TR.`gera_registro_fecha`>=? AND TR.`gera_registro_fecha`<='".$fecha_fin." 23:59:59' ORDER BY `gerlog_radicado` ASC";
        } elseif ($tipo_reporte=="Alertas") {
            $titulo_reporte="Gestión Encuestas-Alertas ".date('Y-m-d H_i_s').".xlsx";
            $titulo_hoja="Alertas";
            $consulta_string="SELECT `gern_id`, `gern_encuesta`, TE.`ger_encuesta_id`, TE.`ger_radicado`, TM.`gem_nombre_encuesta`, TS1.`gems_nombre_seccion`, TS2.`gems_nombre_seccion`, `gern_address`, `gern_cc`, `gern_bcc`, `gern_subject`, `gern_intentos`, `gern_estado_envio`, `gern_fecha_envio`, `gern_fecha_registro`, `gern_usuario_registro`, TU.`usu_nombres_apellidos` FROM `tb_gestion_encuesta_registro_notificacion` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro_notificacion`.`gern_usuario_registro`=TU.`usu_id` LEFT JOIN `tb_gestion_encuesta_registro` AS TE ON `tb_gestion_encuesta_registro_notificacion`.`gern_encuesta`=TE.`ger_consecutivo` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON TE.`ger_encuesta_id`=TM.`gem_id` LEFT JOIN `tb_gestion_encuestas_matriz_seccion` AS TS1 ON `tb_gestion_encuesta_registro_notificacion`.`gern_seccion`=TS1.`gems_id` LEFT JOIN `tb_gestion_encuestas_matriz_seccion` AS TS2 ON `tb_gestion_encuesta_registro_notificacion`.`gern_seccion_complemento`=TS2.`gems_id` LEFT JOIN `tb_gestion_encuesta_radicado` AS TR ON TE.`ger_radicado`=TR.`gera_radicado` WHERE TE.`ger_encuesta_id`=? ".$filtro_estado." AND TR.`gera_registro_fecha`>=? AND TR.`gera_registro_fecha`<='".$fecha_fin." 23:59:59' ORDER BY `gern_encuesta` ASC";
        }

        $consulta_registros = $enlace_db->prepare($consulta_string);
        $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros->execute();
        $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

        if ($tipo_reporte=="Encuestas") {
            for ($i=0; $i < count($resultado_registros); $i++) { 
                $array_consecutivo_encuesta[]=$resultado_registros[$i][0];
                $array_consecutivo_data[$resultado_registros[$i][0]]['encuesta']=$resultado_registros[$i][1];
                $array_consecutivo_data[$resultado_registros[$i][0]]['radicado']=$resultado_registros[$i][2];
                $array_consecutivo_data[$resultado_registros[$i][0]]['contactado']=$resultado_registros[$i][3];
                $array_consecutivo_data[$resultado_registros[$i][0]]['efectivo']=$resultado_registros[$i][4];
                $array_consecutivo_data[$resultado_registros[$i][0]]['observaciones']=$resultado_registros[$i][5];
                $array_consecutivo_data[$resultado_registros[$i][0]]['usuario_id']=$resultado_registros[$i][6];
                $array_consecutivo_data[$resultado_registros[$i][0]]['usuario_nombre']=$resultado_registros[$i][7];
                $array_consecutivo_data[$resultado_registros[$i][0]]['fecha_registro']=$resultado_registros[$i][8];

                $array_consecutivo_data[$resultado_registros[$i][0]]['matriz']=$array_matriz;
            }

            $array_consecutivo_encuesta=array_values(array_unique($array_consecutivo_encuesta));

            for ($i=0; $i < count($resultado_registros); $i++) { 
                $respuesta="";
                if ($resultado_registros[$i][13]=="Varias opciones" OR $resultado_registros[$i][13]=="Casillas" OR $resultado_registros[$i][13]=="Desplegable") {
                    $respuesta=$resultado_registros[$i][14];
                } else {
                    $respuesta=$resultado_registros[$i][12];
                }
                $array_consecutivo_data[$resultado_registros[$i][0]]['matriz'][$resultado_registros[$i][10]][$resultado_registros[$i][11]]=$respuesta;
            }
        }
    }

    // echo "<pre>";
    // print_r($array_consecutivo_encuesta);
    // print_r($array_consecutivo_data);
    // echo "</pre>";

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

    if ($tipo_reporte=="Radicados") {
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
        $spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('X')->setWidth(20);
        
        $spreadsheet->getActiveSheet()->getStyle('A6:X6')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A6:X6');

        // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A6','Radicado');
        $spreadsheet->getActiveSheet()->setCellValue('B6','Campaña/Matriz');
        $spreadsheet->getActiveSheet()->setCellValue('C6','Fecha Petición');
        $spreadsheet->getActiveSheet()->setCellValue('D6','Id Peticionario');
        $spreadsheet->getActiveSheet()->setCellValue('E6','Nombres y Apellidos Peticionario');
        $spreadsheet->getActiveSheet()->setCellValue('F6','Teléfono Fijo');
        $spreadsheet->getActiveSheet()->setCellValue('G6','Teléfono Móvil');
        $spreadsheet->getActiveSheet()->setCellValue('H6','Género');
        $spreadsheet->getActiveSheet()->setCellValue('I6','Edad');
        $spreadsheet->getActiveSheet()->setCellValue('J6','Motivo');
        $spreadsheet->getActiveSheet()->setCellValue('K6','Regional');
        $spreadsheet->getActiveSheet()->setCellValue('L6','Centro Zonal');
        $spreadsheet->getActiveSheet()->setCellValue('M6','Estado Gestión');
        $spreadsheet->getActiveSheet()->setCellValue('N6','Intentos');
        $spreadsheet->getActiveSheet()->setCellValue('O6','Contactado');
        $spreadsheet->getActiveSheet()->setCellValue('P6','Efectivo');
        $spreadsheet->getActiveSheet()->setCellValue('Q6','Última Actualización');
        $spreadsheet->getActiveSheet()->setCellValue('R6','Última Observación');
        $spreadsheet->getActiveSheet()->setCellValue('S6','Complemento 1');
        $spreadsheet->getActiveSheet()->setCellValue('T6','Complemento 2');
        $spreadsheet->getActiveSheet()->setCellValue('U6','Complemento 3');
        $spreadsheet->getActiveSheet()->setCellValue('V6','Id Usuario Gestión');
        $spreadsheet->getActiveSheet()->setCellValue('W6','Nombres y Apellidos Usuario Gestión');
        $spreadsheet->getActiveSheet()->setCellValue('X6','Fecha Registro');
        
        $spreadsheet->getActiveSheet()->setCellValue('A1','Campaña: '.$resultado_registros_campania[0][1]);
        $spreadsheet->getActiveSheet()->setCellValue('A2','Tipo reporte: '.$tipo_reporte);
        $spreadsheet->getActiveSheet()->setCellValue('A3','Estado: '.$estado);
        $spreadsheet->getActiveSheet()->setCellValue('A4','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);
        $spreadsheet->getActiveSheet()->setCellValue('A5','Fecha reporte: '.date('Y-m-d H:i:s'));

        //Ingresar Data consultada a partir de la fila 2

        for ($i=7; $i < count($resultado_registros)+7; $i++) {
            if ($resultado_registros[$i-7][14]=='1') {
                $estado_contactado="Si";
            } elseif ($resultado_registros[$i-7][14]=='0') {
                $estado_contactado="No";
            } else {
                $estado_contactado="";
            }

            if ($resultado_registros[$i-7][15]=='1') {
                $estado_efectivo="Si";
            } elseif ($resultado_registros[$i-7][15]=='0') {
                $estado_efectivo="No";
            } else {
                $estado_efectivo="";
            }

            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-7][0]);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-7][2]);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-7][3]);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-7][4]);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$resultado_registros[$i-7][5]);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$resultado_registros[$i-7][6]);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i,$resultado_registros[$i-7][7]);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i,$resultado_registros[$i-7][8]);
            $spreadsheet->getActiveSheet()->setCellValue('I'.$i,$resultado_registros[$i-7][26]);
            $spreadsheet->getActiveSheet()->setCellValue('J'.$i,$resultado_registros[$i-7][9]);
            $spreadsheet->getActiveSheet()->setCellValue('K'.$i,$resultado_registros[$i-7][21]);
            $spreadsheet->getActiveSheet()->setCellValue('L'.$i,$resultado_registros[$i-7][22]);
            $spreadsheet->getActiveSheet()->setCellValue('M'.$i,$resultado_registros[$i-7][12]);
            $spreadsheet->getActiveSheet()->setCellValue('N'.$i,$resultado_registros[$i-7][13]);
            $spreadsheet->getActiveSheet()->setCellValue('O'.$i,$estado_contactado);
            $spreadsheet->getActiveSheet()->setCellValue('P'.$i,$estado_efectivo);
            $spreadsheet->getActiveSheet()->setCellValue('Q'.$i,$resultado_registros[$i-7][17]);
            $spreadsheet->getActiveSheet()->setCellValue('R'.$i,$resultado_registros[$i-7][16]);
            $spreadsheet->getActiveSheet()->setCellValue('S'.$i,$resultado_registros[$i-7][23]);
            $spreadsheet->getActiveSheet()->setCellValue('T'.$i,$resultado_registros[$i-7][24]);
            $spreadsheet->getActiveSheet()->setCellValue('U'.$i,$resultado_registros[$i-7][25]);
            $spreadsheet->getActiveSheet()->setCellValue('V'.$i,$resultado_registros[$i-7][18]);
            $spreadsheet->getActiveSheet()->setCellValue('W'.$i,$resultado_registros[$i-7][19]);
            $spreadsheet->getActiveSheet()->setCellValue('X'.$i,$resultado_registros[$i-7][20]);
            
        }
    } elseif ($tipo_reporte=="Encuestas") {
        // //Estilos de la Hoja 0
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('4')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('5')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('6')->setRowHeight(20);
        $spreadsheet->getActiveSheet()->getRowDimension('7')->setRowHeight(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        
        $spreadsheet->getActiveSheet()->getStyle('A6:'.$array_columnas[count($array_matriz_pregunta)+8].'7')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->getStyle('A7:'.$array_columnas[count($array_matriz_pregunta)+8].'7')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A7:'.$array_columnas[count($array_matriz_pregunta)+8].'7');

        // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A7','Radicado');
        $spreadsheet->getActiveSheet()->setCellValue('B7','Campaña/Matriz');
        $spreadsheet->getActiveSheet()->setCellValue('C7','Encuesta');
        $spreadsheet->getActiveSheet()->setCellValue('D7','Contactado');
        $spreadsheet->getActiveSheet()->setCellValue('E7','Efectivo');
        $spreadsheet->getActiveSheet()->setCellValue('F7','Observaciones');
        $spreadsheet->getActiveSheet()->setCellValue('G7','Id Usuario Registro');
        $spreadsheet->getActiveSheet()->setCellValue('H7','Nombres y Apellidos Usuario Registro');
        $spreadsheet->getActiveSheet()->setCellValue('I7','Fecha Registro');

        $spreadsheet->getActiveSheet()->setCellValue('A1','Campaña: '.$resultado_registros_campania[0][1]);
        $spreadsheet->getActiveSheet()->setCellValue('A2','Tipo reporte: '.$tipo_reporte);
        $spreadsheet->getActiveSheet()->setCellValue('A3','Estado: '.$estado);
        $spreadsheet->getActiveSheet()->setCellValue('A4','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);
        $spreadsheet->getActiveSheet()->setCellValue('A5','Fecha reporte: '.date('Y-m-d H:i:s'));

        $control_columna=9;
        for ($i=0; $i < count($array_matriz_seccion_id); $i++) {
            $fin_seccion=$control_columna+count($array_matriz_seccion[$array_matriz_seccion_id[$i]]['preguntas'])-1;
            $spreadsheet->getActiveSheet()->mergeCells($array_columnas[$control_columna].'6:'.$array_columnas[$fin_seccion].'6');

            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'6',$array_matriz_seccion[$array_matriz_seccion_id[$i]]['nombre']);
            for ($j=0; $j < count($array_matriz_seccion[$array_matriz_seccion_id[$i]]['preguntas']); $j++) { 
                $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'7',$array_matriz_pregunta[$array_matriz_seccion[$array_matriz_seccion_id[$i]]['preguntas'][$j]]);
                $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(20);
                $control_columna++;
            }

        }

        for ($k=8; $k < count($array_consecutivo_encuesta)+8; $k++) { 
            $spreadsheet->getActiveSheet()->setCellValue('A'.$k,$array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['radicado']);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$k,$array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['encuesta']);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$k,$array_consecutivo_encuesta[$k-8]);
            // echo $array_consecutivo_encuesta[$k-8];
            // echo "<br>";
            if ($array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['contactado']=='1') {
                $estado_contactado="Si";
            } elseif ($array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['contactado']=='0') {
                $estado_contactado="No";
            } else {
                $estado_contactado="";
            }
            // echo $estado_contactado;
            // echo "<br>";
            if ($array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['efectivo']=='1') {
                $estado_efectivo="Si";
            } elseif ($array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['efectivo']=='0') {
                $estado_efectivo="No";
            } else {
                $estado_efectivo="";
            }

            $spreadsheet->getActiveSheet()->setCellValue('D'.$k,$estado_contactado);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$k,$estado_efectivo);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$k,$array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['observaciones']);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$k,$array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['usuario_id']);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$k,$array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['usuario_nombre']);
            $spreadsheet->getActiveSheet()->setCellValue('I'.$k,$array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['fecha_registro']);
            $control_columna=9;
            for ($i=0; $i < count($array_matriz_seccion_id); $i++) {
                for ($j=0; $j < count($array_matriz_seccion[$array_matriz_seccion_id[$i]]['preguntas']); $j++) { 
                    $id_pregunta=$array_matriz_seccion[$array_matriz_seccion_id[$i]]['preguntas'][$j];
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$k,$array_consecutivo_data[$array_consecutivo_encuesta[$k-8]]['matriz'][$array_matriz_seccion_id[$i]][$id_pregunta]);
                    $control_columna++;
                }

            }
        }
    } elseif ($tipo_reporte=="Control Cambios") {
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
        
        $spreadsheet->getActiveSheet()->getStyle('A6:H6')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A6:H6');

        // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A6','Radicado');
        $spreadsheet->getActiveSheet()->setCellValue('B6','Campaña/Matriz');
        $spreadsheet->getActiveSheet()->setCellValue('C6','Campo');
        $spreadsheet->getActiveSheet()->setCellValue('D6','Anterior');
        $spreadsheet->getActiveSheet()->setCellValue('E6','Nuevo');
        $spreadsheet->getActiveSheet()->setCellValue('F6','Fecha Registro Cambio');
        $spreadsheet->getActiveSheet()->setCellValue('G6','Id Usuario Cambio');
        $spreadsheet->getActiveSheet()->setCellValue('H6','Nombres y Apellidos Usuario Cambio');

        $spreadsheet->getActiveSheet()->setCellValue('A1','Campaña: '.$resultado_registros_campania[0][1]);
        $spreadsheet->getActiveSheet()->setCellValue('A2','Tipo reporte: '.$tipo_reporte);
        $spreadsheet->getActiveSheet()->setCellValue('A3','Estado: '.$estado);
        $spreadsheet->getActiveSheet()->setCellValue('A4','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);
        $spreadsheet->getActiveSheet()->setCellValue('A5','Fecha reporte: '.date('Y-m-d H:i:s'));
        
        //Ingresar Data consultada a partir de la fila 2

        for ($i=7; $i < count($resultado_registros)+7; $i++) {
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-7][1]);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-7][2]);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-7][3]);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-7][4]);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$resultado_registros[$i-7][5]);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$resultado_registros[$i-7][8]);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i,$resultado_registros[$i-7][6]);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i,$resultado_registros[$i-7][7]);
                        
        }
    } elseif ($tipo_reporte=="Alertas") {
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
        
        $spreadsheet->getActiveSheet()->getStyle('A6:N6')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A6:N6');

        // Escribiendo los titulos
        $spreadsheet->getActiveSheet()->setCellValue('A6','Radicado');
        $spreadsheet->getActiveSheet()->setCellValue('B6','Campaña/Matriz');
        $spreadsheet->getActiveSheet()->setCellValue('C6','Encuesta');
        $spreadsheet->getActiveSheet()->setCellValue('D6','Sección');
        $spreadsheet->getActiveSheet()->setCellValue('E6','Sección Complementaria');
        $spreadsheet->getActiveSheet()->setCellValue('F6','Asunto');
        $spreadsheet->getActiveSheet()->setCellValue('G6','TO');
        $spreadsheet->getActiveSheet()->setCellValue('H6','CC');
        $spreadsheet->getActiveSheet()->setCellValue('I6','BCC');
        $spreadsheet->getActiveSheet()->setCellValue('J6','Estado Envío');
        $spreadsheet->getActiveSheet()->setCellValue('K6','Fecha Envío');
        $spreadsheet->getActiveSheet()->setCellValue('L6','Fecha Registro Notificación');
        $spreadsheet->getActiveSheet()->setCellValue('M6','Id Usuario Registro Notificación');
        $spreadsheet->getActiveSheet()->setCellValue('N6','Nombres y Apellidos Usuario Registro Notificación');

        $spreadsheet->getActiveSheet()->setCellValue('A1','Campaña: '.$resultado_registros_campania[0][1]);
        $spreadsheet->getActiveSheet()->setCellValue('A2','Tipo reporte: '.$tipo_reporte);
        $spreadsheet->getActiveSheet()->setCellValue('A3','Estado: '.$estado);
        $spreadsheet->getActiveSheet()->setCellValue('A4','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);
        $spreadsheet->getActiveSheet()->setCellValue('A5','Fecha reporte: '.date('Y-m-d H:i:s'));
        
        //Ingresar Data consultada a partir de la fila 2

        for ($i=7; $i < count($resultado_registros)+7; $i++) {
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-7][3]);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-7][4]);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-7][1]);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-7][5]);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$resultado_registros[$i-7][6]);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$resultado_registros[$i-7][10]);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i,$resultado_registros[$i-7][7]);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i,$resultado_registros[$i-7][8]);
            $spreadsheet->getActiveSheet()->setCellValue('I'.$i,$resultado_registros[$i-7][9]);
            $spreadsheet->getActiveSheet()->setCellValue('J'.$i,$resultado_registros[$i-7][12]);
            $spreadsheet->getActiveSheet()->setCellValue('K'.$i,$resultado_registros[$i-7][13]);
            $spreadsheet->getActiveSheet()->setCellValue('L'.$i,$resultado_registros[$i-7][14]);
            $spreadsheet->getActiveSheet()->setCellValue('M'.$i,$resultado_registros[$i-7][15]);
            $spreadsheet->getActiveSheet()->setCellValue('N'.$i,$resultado_registros[$i-7][16]);
                        
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