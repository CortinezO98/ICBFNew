<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Auditoría-Monitoreos";
    require_once("../config/validaciones_seguridad.php");
    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');
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
        $fecha_inicio=validar_input($_POST['fecha_inicio']);
        $fecha_fin=validar_input($_POST['fecha_fin']).' 23:59:59';
        $tipo_reporte=validar_input($_POST['tipo_reporte']);
        $titulo_reporte="Gestión Calidad-Auditoría ".date('Y-m-d H_i_s').".xlsx";
        
        if ($tipo_reporte=='Auditoría') {
            // Inicializa variable tipo array
            $data_consulta=array();
            array_push($data_consulta, $fecha_inicio);
            array_push($data_consulta, $fecha_fin);

            $consulta_string="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_nota_general`, TMC.`gcm_nota_enc_estado`, TMC.`gcm_nota_ecn_estado`, TMC.`gcm_nota_ecuf_estado`, TMC.`gcm_aplica_indicador`, TMC.`gcm_fecha_monitoreo`, TMC.`gcm_registro_usuario`, TSA.`usu_nombres_apellidos`, TMA.`gcm_observaciones_monitoreo`, TMA.`gcm_nota_enc`, TMA.`gcm_nota_ecn`, TMA.`gcm_nota_ecuf`, TMA.`gcm_estado`, TMA.`gcm_registro_fecha`, TMA.`gcm_nota_general`, TMA.`gcm_nota_enc_estado`, TMA.`gcm_nota_ecn_estado`, TMA.`gcm_nota_ecuf_estado`, TMA.`gcm_afectacion_1`, TMA.`gcm_fecha_monitoreo`, TMA.`gcm_registro_usuario`, TURA.`usu_nombres_apellidos`, TMC.`gcm_responsable`, TMC.`gcm_analista` FROM `tb_gestion_calidad_monitoreo` AS TMC 
                LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` 
                LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` 
                LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` 
                LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` 
                LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` 
                LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` 
                LEFT JOIN `tb_gestion_auditoria_monitoreo` AS TMA ON TMC.`gcm_id`=TMA.`gcm_id` 
                LEFT JOIN `tb_administrador_usuario` AS TURA ON TMA.`gcm_registro_usuario`=TURA.`usu_id` 
                WHERE 1=1 AND TMC.`gcm_auditoria`='Si' AND TMC.`gcm_registro_fecha`>=? AND TMC.`gcm_registro_fecha`<=? ORDER BY TMC.`gcm_id`";

            $consulta_registros = $enlace_db->prepare($consulta_string);
            if (count($data_consulta)>0) {
                // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
                $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
                
            }
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

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
            $spreadsheet->getActiveSheet()->setTitle('Reporte Gestión Calidad');

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
            $spreadsheet->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AA')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AC')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AD')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AE')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AF')->setWidth(20);
            $spreadsheet->getActiveSheet()->getStyle('A3:Y3')->applyFromArray($styleArrayTitulos);
            $spreadsheet->getActiveSheet()->setAutoFilter('A3:Y3');
            $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);

            // Escribiendo los titulos
            $spreadsheet->getActiveSheet()->setCellValue('A3','Consecutivo');
            $spreadsheet->getActiveSheet()->setCellValue('B3','Matriz');
            $spreadsheet->getActiveSheet()->setCellValue('C3','Tipo Monitoreo');
            $spreadsheet->getActiveSheet()->setCellValue('D3','Skill Interacción');
            $spreadsheet->getActiveSheet()->setCellValue('E3','Tipo Gestión');
            $spreadsheet->getActiveSheet()->setCellValue('F3','Segmento');
            $spreadsheet->getActiveSheet()->setCellValue('G3','Id SIM');
            $spreadsheet->getActiveSheet()->setCellValue('H3','Id/ANI');
            $spreadsheet->getActiveSheet()->setCellValue('I3','Agente-Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('J3','Agente-Documento');
            $spreadsheet->getActiveSheet()->setCellValue('K3','Supervisor-Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('L3','Supervisor-Documento');
            $spreadsheet->getActiveSheet()->setCellValue('M3','Líder Calidad-Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('N3','Líder Calidad-Documento');
            $spreadsheet->getActiveSheet()->setCellValue('O3','Fecha Gestión');
            $spreadsheet->getActiveSheet()->setCellValue('P3','Fecha Monitoreo');
            $spreadsheet->getActiveSheet()->setCellValue('Q3','Fecha Auditoría');
            $spreadsheet->getActiveSheet()->setCellValue('R3','Nota ECUF');
            $spreadsheet->getActiveSheet()->setCellValue('S3','Nota ECN');
            $spreadsheet->getActiveSheet()->setCellValue('T3','Nota ENC');
            $spreadsheet->getActiveSheet()->setCellValue('U3','Nota General');
            $spreadsheet->getActiveSheet()->setCellValue('V3','Estado');
            $spreadsheet->getActiveSheet()->setCellValue('W3','Observaciones');
            $spreadsheet->getActiveSheet()->setCellValue('X3','Usuario Registro');
            $spreadsheet->getActiveSheet()->setCellValue('Y3','Fecha-Hora Registro');
            
            //Ingresar Data consultada a partir de la fila 4

            for ($i=4; $i < count($resultado_registros)+4; $i++) {
                $fecha_auditoria='';
                if ($resultado_registros[$i-4][39]!='') {
                    $fecha_auditoria=date('d/m/Y', strtotime($resultado_registros[$i-4][39]));
                }

                $fecha_registro='';
                if ($resultado_registros[$i-4][33]!='') {
                    $fecha_registro=date('d/m/Y H:i:s', strtotime($resultado_registros[$i-4][33]));
                }


                $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-4][0]);
                $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-4][1]);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-4][5]);
                $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-4][6]);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$resultado_registros[$i-4][7]);
                $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$resultado_registros[$i-4][8]);
                $spreadsheet->getActiveSheet()->setCellValue('G'.$i,$resultado_registros[$i-4][9]);
                $spreadsheet->getActiveSheet()->setCellValue('H'.$i,$resultado_registros[$i-4][10]);
                $spreadsheet->getActiveSheet()->setCellValue('I'.$i,$resultado_registros[$i-4][2]);
                $spreadsheet->getActiveSheet()->setCellValue('J'.$i,$resultado_registros[$i-4][43]);
                $spreadsheet->getActiveSheet()->setCellValue('K'.$i,$resultado_registros[$i-4][19]);
                $spreadsheet->getActiveSheet()->setCellValue('L'.$i,$resultado_registros[$i-4][42]);
                $spreadsheet->getActiveSheet()->setCellValue('M'.$i,$resultado_registros[$i-4][16]);
                $spreadsheet->getActiveSheet()->setCellValue('N'.$i,$resultado_registros[$i-4][26]);
                $spreadsheet->getActiveSheet()->setCellValue('O'.$i,date('d/m/Y', strtotime($resultado_registros[$i-4][3])));
                $spreadsheet->getActiveSheet()->setCellValue('P'.$i,date('d/m/Y', strtotime($resultado_registros[$i-4][25])));
                $spreadsheet->getActiveSheet()->setCellValue('Q'.$i,$fecha_auditoria);
                $spreadsheet->getActiveSheet()->setCellValue('R'.$i,$resultado_registros[$i-4][31]);
                $spreadsheet->getActiveSheet()->setCellValue('S'.$i,$resultado_registros[$i-4][30]);
                $spreadsheet->getActiveSheet()->setCellValue('T'.$i,$resultado_registros[$i-4][29]);
                $spreadsheet->getActiveSheet()->setCellValue('U'.$i,$resultado_registros[$i-4][34]);
                $spreadsheet->getActiveSheet()->setCellValue('V'.$i,$resultado_registros[$i-4][32]);
                $spreadsheet->getActiveSheet()->setCellValue('W'.$i,$resultado_registros[$i-4][28]);
                $spreadsheet->getActiveSheet()->setCellValue('X'.$i,$resultado_registros[$i-4][41]);
                $spreadsheet->getActiveSheet()->setCellValue('Y'.$i,$fecha_registro);
            }

            //Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$titulo_reporte.'"');
            header('Cache-Control: max-age=0');

            // Guardamos el archivo, en este caso lo guarda con el mismo nombre del php
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }
    }
?>