<?php
    //Arrays convenciones turnos
      $array_convenciones['INCA']='Incapacidad';
      $array_convenciones['LICL']='Licencia por luto';
      $array_convenciones['CALD']='Calamidad doméstica';
      $array_convenciones['LICM']='Licencia maternidad';
      $array_convenciones['AUSI']='Ausencia injustificada';
      $array_convenciones['LLET']='Llegada tarde';
      $array_convenciones['PERM']='Permiso';
      $array_convenciones['VACA']='Vacaciones';
      $array_convenciones['RETI']='Retiro';
      $array_convenciones['DESC']='Descanso';
      $array_convenciones['SUSP']='Suspensión';
      $array_convenciones['CUMP']='Cumpleaños';
      $array_convenciones['BENE']='Beneficio';

      $array_convenciones_color['turno']='#196F3D';
      $array_convenciones_color['INCA']='#21618C';
      $array_convenciones_color['LICL']='#212F3C';
      $array_convenciones_color['CALD']='#515A5A';
      $array_convenciones_color['LICM']='#B9770E';
      $array_convenciones_color['AUSI']='#B03A2E';
      $array_convenciones_color['LLET']='#E67E22';
      $array_convenciones_color['PERM']='#117A65';
      $array_convenciones_color['VACA']='#2980B9';
      $array_convenciones_color['RETI']='#1C2833';
      $array_convenciones_color['DESC']='#5F6A6A';
      $array_convenciones_color['SUSP']='#A93226';
      $array_convenciones_color['CUMP']='#F1C40F';
      $array_convenciones_color['BENE']='#8E44AD';
      $array_convenciones_color['Diurno']='#D4AC0D';
      $array_convenciones_color['Nocturno']='#1C2833';

      $array_colores_turnos['turno']='#1E8449';
      $array_colores_turnos['almuerzo']='#2874A6';
      $array_colores_turnos['break']='#F1C40F';
      $array_colores_turnos['pausaactiva']='#B03A2E';
      $array_colores_turnos['capacitacion']='#6C3483';
      $array_colores_turnos['retroalimentacion']='#1ABC9C';
      
      $array_iconos_turnos['turno']='user-clock';
      $array_iconos_turnos['almuerzo']='utensils';
      $array_iconos_turnos['break']='coffee';
      $array_iconos_turnos['pausaactiva']='walking';
      $array_iconos_turnos['capacitacion']='chalkboard-teacher';
      $array_iconos_turnos['retroalimentacion']='retweet';

      $array_nombres_turnos['turno']='Turno';
      $array_nombres_turnos['almuerzo']='Almuerzo';
      $array_nombres_turnos['break']='Break';
      $array_nombres_turnos['pausaactiva']='Pausa Activa';
      $array_nombres_turnos['capacitacion']='Capacitación';
      $array_nombres_turnos['retroalimentacion']='Retroalimentación';

    //Arrays
    $array_dias = array(1 => "Lu", 2 => "Ma", 3 => "Mi", 4 => "Ju", 5 => "Vi", 6 => "Sá", 0 => "Do");
    $array_mes = array(1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre");
    $array_mes_min = array(1 => "Ene", 2 => "Feb", 3 => "Mar", 4 => "Abr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dic");

    // Arrays recargos horario
        $array_horarios['00:01']='1';
        $array_horarios['00:02']='2';
        $array_horarios['00:03']='3';
        $array_horarios['00:04']='4';
        $array_horarios['00:05']='5';
        $array_horarios['00:06']='6';
        $array_horarios['00:07']='7';
        $array_horarios['00:08']='8';
        $array_horarios['00:09']='9';
        $array_horarios['00:10']='10';
        $array_horarios['00:11']='11';
        $array_horarios['00:12']='12';
        $array_horarios['00:13']='13';
        $array_horarios['00:14']='14';
        $array_horarios['00:15']='15';
        $array_horarios['00:16']='16';
        $array_horarios['00:17']='17';
        $array_horarios['00:18']='18';
        $array_horarios['00:19']='19';
        $array_horarios['00:20']='20';
        $array_horarios['00:21']='21';
        $array_horarios['00:22']='22';
        $array_horarios['00:23']='23';
        $array_horarios['00:24']='24';
        $array_horarios['00:25']='25';
        $array_horarios['00:26']='26';
        $array_horarios['00:27']='27';
        $array_horarios['00:28']='28';
        $array_horarios['00:29']='29';
        $array_horarios['00:30']='30';
        $array_horarios['00:31']='31';
        $array_horarios['00:32']='32';
        $array_horarios['00:33']='33';
        $array_horarios['00:34']='34';
        $array_horarios['00:35']='35';
        $array_horarios['00:36']='36';
        $array_horarios['00:37']='37';
        $array_horarios['00:38']='38';
        $array_horarios['00:39']='39';
        $array_horarios['00:40']='40';
        $array_horarios['00:41']='41';
        $array_horarios['00:42']='42';
        $array_horarios['00:43']='43';
        $array_horarios['00:44']='44';
        $array_horarios['00:45']='45';
        $array_horarios['00:46']='46';
        $array_horarios['00:47']='47';
        $array_horarios['00:48']='48';
        $array_horarios['00:49']='49';
        $array_horarios['00:50']='50';
        $array_horarios['00:51']='51';
        $array_horarios['00:52']='52';
        $array_horarios['00:53']='53';
        $array_horarios['00:54']='54';
        $array_horarios['00:55']='55';
        $array_horarios['00:56']='56';
        $array_horarios['00:57']='57';
        $array_horarios['00:58']='58';
        $array_horarios['00:59']='59';
        $array_horarios['01:00']='60';
        $array_horarios['01:01']='61';
        $array_horarios['01:02']='62';
        $array_horarios['01:03']='63';
        $array_horarios['01:04']='64';
        $array_horarios['01:05']='65';
        $array_horarios['01:06']='66';
        $array_horarios['01:07']='67';
        $array_horarios['01:08']='68';
        $array_horarios['01:09']='69';
        $array_horarios['01:10']='70';
        $array_horarios['01:11']='71';
        $array_horarios['01:12']='72';
        $array_horarios['01:13']='73';
        $array_horarios['01:14']='74';
        $array_horarios['01:15']='75';
        $array_horarios['01:16']='76';
        $array_horarios['01:17']='77';
        $array_horarios['01:18']='78';
        $array_horarios['01:19']='79';
        $array_horarios['01:20']='80';
        $array_horarios['01:21']='81';
        $array_horarios['01:22']='82';
        $array_horarios['01:23']='83';
        $array_horarios['01:24']='84';
        $array_horarios['01:25']='85';
        $array_horarios['01:26']='86';
        $array_horarios['01:27']='87';
        $array_horarios['01:28']='88';
        $array_horarios['01:29']='89';
        $array_horarios['01:30']='90';
        $array_horarios['01:31']='91';
        $array_horarios['01:32']='92';
        $array_horarios['01:33']='93';
        $array_horarios['01:34']='94';
        $array_horarios['01:35']='95';
        $array_horarios['01:36']='96';
        $array_horarios['01:37']='97';
        $array_horarios['01:38']='98';
        $array_horarios['01:39']='99';
        $array_horarios['01:40']='100';
        $array_horarios['01:41']='101';
        $array_horarios['01:42']='102';
        $array_horarios['01:43']='103';
        $array_horarios['01:44']='104';
        $array_horarios['01:45']='105';
        $array_horarios['01:46']='106';
        $array_horarios['01:47']='107';
        $array_horarios['01:48']='108';
        $array_horarios['01:49']='109';
        $array_horarios['01:50']='110';
        $array_horarios['01:51']='111';
        $array_horarios['01:52']='112';
        $array_horarios['01:53']='113';
        $array_horarios['01:54']='114';
        $array_horarios['01:55']='115';
        $array_horarios['01:56']='116';
        $array_horarios['01:57']='117';
        $array_horarios['01:58']='118';
        $array_horarios['01:59']='119';
        $array_horarios['02:00']='120';
        $array_horarios['02:01']='121';
        $array_horarios['02:02']='122';
        $array_horarios['02:03']='123';
        $array_horarios['02:04']='124';
        $array_horarios['02:05']='125';
        $array_horarios['02:06']='126';
        $array_horarios['02:07']='127';
        $array_horarios['02:08']='128';
        $array_horarios['02:09']='129';
        $array_horarios['02:10']='130';
        $array_horarios['02:11']='131';
        $array_horarios['02:12']='132';
        $array_horarios['02:13']='133';
        $array_horarios['02:14']='134';
        $array_horarios['02:15']='135';
        $array_horarios['02:16']='136';
        $array_horarios['02:17']='137';
        $array_horarios['02:18']='138';
        $array_horarios['02:19']='139';
        $array_horarios['02:20']='140';
        $array_horarios['02:21']='141';
        $array_horarios['02:22']='142';
        $array_horarios['02:23']='143';
        $array_horarios['02:24']='144';
        $array_horarios['02:25']='145';
        $array_horarios['02:26']='146';
        $array_horarios['02:27']='147';
        $array_horarios['02:28']='148';
        $array_horarios['02:29']='149';
        $array_horarios['02:30']='150';
        $array_horarios['02:31']='151';
        $array_horarios['02:32']='152';
        $array_horarios['02:33']='153';
        $array_horarios['02:34']='154';
        $array_horarios['02:35']='155';
        $array_horarios['02:36']='156';
        $array_horarios['02:37']='157';
        $array_horarios['02:38']='158';
        $array_horarios['02:39']='159';
        $array_horarios['02:40']='160';
        $array_horarios['02:41']='161';
        $array_horarios['02:42']='162';
        $array_horarios['02:43']='163';
        $array_horarios['02:44']='164';
        $array_horarios['02:45']='165';
        $array_horarios['02:46']='166';
        $array_horarios['02:47']='167';
        $array_horarios['02:48']='168';
        $array_horarios['02:49']='169';
        $array_horarios['02:50']='170';
        $array_horarios['02:51']='171';
        $array_horarios['02:52']='172';
        $array_horarios['02:53']='173';
        $array_horarios['02:54']='174';
        $array_horarios['02:55']='175';
        $array_horarios['02:56']='176';
        $array_horarios['02:57']='177';
        $array_horarios['02:58']='178';
        $array_horarios['02:59']='179';
        $array_horarios['03:00']='180';
        $array_horarios['03:01']='181';
        $array_horarios['03:02']='182';
        $array_horarios['03:03']='183';
        $array_horarios['03:04']='184';
        $array_horarios['03:05']='185';
        $array_horarios['03:06']='186';
        $array_horarios['03:07']='187';
        $array_horarios['03:08']='188';
        $array_horarios['03:09']='189';
        $array_horarios['03:10']='190';
        $array_horarios['03:11']='191';
        $array_horarios['03:12']='192';
        $array_horarios['03:13']='193';
        $array_horarios['03:14']='194';
        $array_horarios['03:15']='195';
        $array_horarios['03:16']='196';
        $array_horarios['03:17']='197';
        $array_horarios['03:18']='198';
        $array_horarios['03:19']='199';
        $array_horarios['03:20']='200';
        $array_horarios['03:21']='201';
        $array_horarios['03:22']='202';
        $array_horarios['03:23']='203';
        $array_horarios['03:24']='204';
        $array_horarios['03:25']='205';
        $array_horarios['03:26']='206';
        $array_horarios['03:27']='207';
        $array_horarios['03:28']='208';
        $array_horarios['03:29']='209';
        $array_horarios['03:30']='210';
        $array_horarios['03:31']='211';
        $array_horarios['03:32']='212';
        $array_horarios['03:33']='213';
        $array_horarios['03:34']='214';
        $array_horarios['03:35']='215';
        $array_horarios['03:36']='216';
        $array_horarios['03:37']='217';
        $array_horarios['03:38']='218';
        $array_horarios['03:39']='219';
        $array_horarios['03:40']='220';
        $array_horarios['03:41']='221';
        $array_horarios['03:42']='222';
        $array_horarios['03:43']='223';
        $array_horarios['03:44']='224';
        $array_horarios['03:45']='225';
        $array_horarios['03:46']='226';
        $array_horarios['03:47']='227';
        $array_horarios['03:48']='228';
        $array_horarios['03:49']='229';
        $array_horarios['03:50']='230';
        $array_horarios['03:51']='231';
        $array_horarios['03:52']='232';
        $array_horarios['03:53']='233';
        $array_horarios['03:54']='234';
        $array_horarios['03:55']='235';
        $array_horarios['03:56']='236';
        $array_horarios['03:57']='237';
        $array_horarios['03:58']='238';
        $array_horarios['03:59']='239';
        $array_horarios['04:00']='240';
        $array_horarios['04:01']='241';
        $array_horarios['04:02']='242';
        $array_horarios['04:03']='243';
        $array_horarios['04:04']='244';
        $array_horarios['04:05']='245';
        $array_horarios['04:06']='246';
        $array_horarios['04:07']='247';
        $array_horarios['04:08']='248';
        $array_horarios['04:09']='249';
        $array_horarios['04:10']='250';
        $array_horarios['04:11']='251';
        $array_horarios['04:12']='252';
        $array_horarios['04:13']='253';
        $array_horarios['04:14']='254';
        $array_horarios['04:15']='255';
        $array_horarios['04:16']='256';
        $array_horarios['04:17']='257';
        $array_horarios['04:18']='258';
        $array_horarios['04:19']='259';
        $array_horarios['04:20']='260';
        $array_horarios['04:21']='261';
        $array_horarios['04:22']='262';
        $array_horarios['04:23']='263';
        $array_horarios['04:24']='264';
        $array_horarios['04:25']='265';
        $array_horarios['04:26']='266';
        $array_horarios['04:27']='267';
        $array_horarios['04:28']='268';
        $array_horarios['04:29']='269';
        $array_horarios['04:30']='270';
        $array_horarios['04:31']='271';
        $array_horarios['04:32']='272';
        $array_horarios['04:33']='273';
        $array_horarios['04:34']='274';
        $array_horarios['04:35']='275';
        $array_horarios['04:36']='276';
        $array_horarios['04:37']='277';
        $array_horarios['04:38']='278';
        $array_horarios['04:39']='279';
        $array_horarios['04:40']='280';
        $array_horarios['04:41']='281';
        $array_horarios['04:42']='282';
        $array_horarios['04:43']='283';
        $array_horarios['04:44']='284';
        $array_horarios['04:45']='285';
        $array_horarios['04:46']='286';
        $array_horarios['04:47']='287';
        $array_horarios['04:48']='288';
        $array_horarios['04:49']='289';
        $array_horarios['04:50']='290';
        $array_horarios['04:51']='291';
        $array_horarios['04:52']='292';
        $array_horarios['04:53']='293';
        $array_horarios['04:54']='294';
        $array_horarios['04:55']='295';
        $array_horarios['04:56']='296';
        $array_horarios['04:57']='297';
        $array_horarios['04:58']='298';
        $array_horarios['04:59']='299';
        $array_horarios['05:00']='300';
        $array_horarios['05:01']='301';
        $array_horarios['05:02']='302';
        $array_horarios['05:03']='303';
        $array_horarios['05:04']='304';
        $array_horarios['05:05']='305';
        $array_horarios['05:06']='306';
        $array_horarios['05:07']='307';
        $array_horarios['05:08']='308';
        $array_horarios['05:09']='309';
        $array_horarios['05:10']='310';
        $array_horarios['05:11']='311';
        $array_horarios['05:12']='312';
        $array_horarios['05:13']='313';
        $array_horarios['05:14']='314';
        $array_horarios['05:15']='315';
        $array_horarios['05:16']='316';
        $array_horarios['05:17']='317';
        $array_horarios['05:18']='318';
        $array_horarios['05:19']='319';
        $array_horarios['05:20']='320';
        $array_horarios['05:21']='321';
        $array_horarios['05:22']='322';
        $array_horarios['05:23']='323';
        $array_horarios['05:24']='324';
        $array_horarios['05:25']='325';
        $array_horarios['05:26']='326';
        $array_horarios['05:27']='327';
        $array_horarios['05:28']='328';
        $array_horarios['05:29']='329';
        $array_horarios['05:30']='330';
        $array_horarios['05:31']='331';
        $array_horarios['05:32']='332';
        $array_horarios['05:33']='333';
        $array_horarios['05:34']='334';
        $array_horarios['05:35']='335';
        $array_horarios['05:36']='336';
        $array_horarios['05:37']='337';
        $array_horarios['05:38']='338';
        $array_horarios['05:39']='339';
        $array_horarios['05:40']='340';
        $array_horarios['05:41']='341';
        $array_horarios['05:42']='342';
        $array_horarios['05:43']='343';
        $array_horarios['05:44']='344';
        $array_horarios['05:45']='345';
        $array_horarios['05:46']='346';
        $array_horarios['05:47']='347';
        $array_horarios['05:48']='348';
        $array_horarios['05:49']='349';
        $array_horarios['05:50']='350';
        $array_horarios['05:51']='351';
        $array_horarios['05:52']='352';
        $array_horarios['05:53']='353';
        $array_horarios['05:54']='354';
        $array_horarios['05:55']='355';
        $array_horarios['05:56']='356';
        $array_horarios['05:57']='357';
        $array_horarios['05:58']='358';
        $array_horarios['05:59']='359';
        $array_horarios['06:00']='360';
        $array_horarios['06:01']='361';
        $array_horarios['06:02']='362';
        $array_horarios['06:03']='363';
        $array_horarios['06:04']='364';
        $array_horarios['06:05']='365';
        $array_horarios['06:06']='366';
        $array_horarios['06:07']='367';
        $array_horarios['06:08']='368';
        $array_horarios['06:09']='369';
        $array_horarios['06:10']='370';
        $array_horarios['06:11']='371';
        $array_horarios['06:12']='372';
        $array_horarios['06:13']='373';
        $array_horarios['06:14']='374';
        $array_horarios['06:15']='375';
        $array_horarios['06:16']='376';
        $array_horarios['06:17']='377';
        $array_horarios['06:18']='378';
        $array_horarios['06:19']='379';
        $array_horarios['06:20']='380';
        $array_horarios['06:21']='381';
        $array_horarios['06:22']='382';
        $array_horarios['06:23']='383';
        $array_horarios['06:24']='384';
        $array_horarios['06:25']='385';
        $array_horarios['06:26']='386';
        $array_horarios['06:27']='387';
        $array_horarios['06:28']='388';
        $array_horarios['06:29']='389';
        $array_horarios['06:30']='390';
        $array_horarios['06:31']='391';
        $array_horarios['06:32']='392';
        $array_horarios['06:33']='393';
        $array_horarios['06:34']='394';
        $array_horarios['06:35']='395';
        $array_horarios['06:36']='396';
        $array_horarios['06:37']='397';
        $array_horarios['06:38']='398';
        $array_horarios['06:39']='399';
        $array_horarios['06:40']='400';
        $array_horarios['06:41']='401';
        $array_horarios['06:42']='402';
        $array_horarios['06:43']='403';
        $array_horarios['06:44']='404';
        $array_horarios['06:45']='405';
        $array_horarios['06:46']='406';
        $array_horarios['06:47']='407';
        $array_horarios['06:48']='408';
        $array_horarios['06:49']='409';
        $array_horarios['06:50']='410';
        $array_horarios['06:51']='411';
        $array_horarios['06:52']='412';
        $array_horarios['06:53']='413';
        $array_horarios['06:54']='414';
        $array_horarios['06:55']='415';
        $array_horarios['06:56']='416';
        $array_horarios['06:57']='417';
        $array_horarios['06:58']='418';
        $array_horarios['06:59']='419';
        $array_horarios['07:00']='420';
        $array_horarios['07:01']='421';
        $array_horarios['07:02']='422';
        $array_horarios['07:03']='423';
        $array_horarios['07:04']='424';
        $array_horarios['07:05']='425';
        $array_horarios['07:06']='426';
        $array_horarios['07:07']='427';
        $array_horarios['07:08']='428';
        $array_horarios['07:09']='429';
        $array_horarios['07:10']='430';
        $array_horarios['07:11']='431';
        $array_horarios['07:12']='432';
        $array_horarios['07:13']='433';
        $array_horarios['07:14']='434';
        $array_horarios['07:15']='435';
        $array_horarios['07:16']='436';
        $array_horarios['07:17']='437';
        $array_horarios['07:18']='438';
        $array_horarios['07:19']='439';
        $array_horarios['07:20']='440';
        $array_horarios['07:21']='441';
        $array_horarios['07:22']='442';
        $array_horarios['07:23']='443';
        $array_horarios['07:24']='444';
        $array_horarios['07:25']='445';
        $array_horarios['07:26']='446';
        $array_horarios['07:27']='447';
        $array_horarios['07:28']='448';
        $array_horarios['07:29']='449';
        $array_horarios['07:30']='450';
        $array_horarios['07:31']='451';
        $array_horarios['07:32']='452';
        $array_horarios['07:33']='453';
        $array_horarios['07:34']='454';
        $array_horarios['07:35']='455';
        $array_horarios['07:36']='456';
        $array_horarios['07:37']='457';
        $array_horarios['07:38']='458';
        $array_horarios['07:39']='459';
        $array_horarios['07:40']='460';
        $array_horarios['07:41']='461';
        $array_horarios['07:42']='462';
        $array_horarios['07:43']='463';
        $array_horarios['07:44']='464';
        $array_horarios['07:45']='465';
        $array_horarios['07:46']='466';
        $array_horarios['07:47']='467';
        $array_horarios['07:48']='468';
        $array_horarios['07:49']='469';
        $array_horarios['07:50']='470';
        $array_horarios['07:51']='471';
        $array_horarios['07:52']='472';
        $array_horarios['07:53']='473';
        $array_horarios['07:54']='474';
        $array_horarios['07:55']='475';
        $array_horarios['07:56']='476';
        $array_horarios['07:57']='477';
        $array_horarios['07:58']='478';
        $array_horarios['07:59']='479';
        $array_horarios['08:00']='480';
        $array_horarios['08:01']='481';
        $array_horarios['08:02']='482';
        $array_horarios['08:03']='483';
        $array_horarios['08:04']='484';
        $array_horarios['08:05']='485';
        $array_horarios['08:06']='486';
        $array_horarios['08:07']='487';
        $array_horarios['08:08']='488';
        $array_horarios['08:09']='489';
        $array_horarios['08:10']='490';
        $array_horarios['08:11']='491';
        $array_horarios['08:12']='492';
        $array_horarios['08:13']='493';
        $array_horarios['08:14']='494';
        $array_horarios['08:15']='495';
        $array_horarios['08:16']='496';
        $array_horarios['08:17']='497';
        $array_horarios['08:18']='498';
        $array_horarios['08:19']='499';
        $array_horarios['08:20']='500';
        $array_horarios['08:21']='501';
        $array_horarios['08:22']='502';
        $array_horarios['08:23']='503';
        $array_horarios['08:24']='504';
        $array_horarios['08:25']='505';
        $array_horarios['08:26']='506';
        $array_horarios['08:27']='507';
        $array_horarios['08:28']='508';
        $array_horarios['08:29']='509';
        $array_horarios['08:30']='510';
        $array_horarios['08:31']='511';
        $array_horarios['08:32']='512';
        $array_horarios['08:33']='513';
        $array_horarios['08:34']='514';
        $array_horarios['08:35']='515';
        $array_horarios['08:36']='516';
        $array_horarios['08:37']='517';
        $array_horarios['08:38']='518';
        $array_horarios['08:39']='519';
        $array_horarios['08:40']='520';
        $array_horarios['08:41']='521';
        $array_horarios['08:42']='522';
        $array_horarios['08:43']='523';
        $array_horarios['08:44']='524';
        $array_horarios['08:45']='525';
        $array_horarios['08:46']='526';
        $array_horarios['08:47']='527';
        $array_horarios['08:48']='528';
        $array_horarios['08:49']='529';
        $array_horarios['08:50']='530';
        $array_horarios['08:51']='531';
        $array_horarios['08:52']='532';
        $array_horarios['08:53']='533';
        $array_horarios['08:54']='534';
        $array_horarios['08:55']='535';
        $array_horarios['08:56']='536';
        $array_horarios['08:57']='537';
        $array_horarios['08:58']='538';
        $array_horarios['08:59']='539';
        $array_horarios['09:00']='540';
        $array_horarios['09:01']='541';
        $array_horarios['09:02']='542';
        $array_horarios['09:03']='543';
        $array_horarios['09:04']='544';
        $array_horarios['09:05']='545';
        $array_horarios['09:06']='546';
        $array_horarios['09:07']='547';
        $array_horarios['09:08']='548';
        $array_horarios['09:09']='549';
        $array_horarios['09:10']='550';
        $array_horarios['09:11']='551';
        $array_horarios['09:12']='552';
        $array_horarios['09:13']='553';
        $array_horarios['09:14']='554';
        $array_horarios['09:15']='555';
        $array_horarios['09:16']='556';
        $array_horarios['09:17']='557';
        $array_horarios['09:18']='558';
        $array_horarios['09:19']='559';
        $array_horarios['09:20']='560';
        $array_horarios['09:21']='561';
        $array_horarios['09:22']='562';
        $array_horarios['09:23']='563';
        $array_horarios['09:24']='564';
        $array_horarios['09:25']='565';
        $array_horarios['09:26']='566';
        $array_horarios['09:27']='567';
        $array_horarios['09:28']='568';
        $array_horarios['09:29']='569';
        $array_horarios['09:30']='570';
        $array_horarios['09:31']='571';
        $array_horarios['09:32']='572';
        $array_horarios['09:33']='573';
        $array_horarios['09:34']='574';
        $array_horarios['09:35']='575';
        $array_horarios['09:36']='576';
        $array_horarios['09:37']='577';
        $array_horarios['09:38']='578';
        $array_horarios['09:39']='579';
        $array_horarios['09:40']='580';
        $array_horarios['09:41']='581';
        $array_horarios['09:42']='582';
        $array_horarios['09:43']='583';
        $array_horarios['09:44']='584';
        $array_horarios['09:45']='585';
        $array_horarios['09:46']='586';
        $array_horarios['09:47']='587';
        $array_horarios['09:48']='588';
        $array_horarios['09:49']='589';
        $array_horarios['09:50']='590';
        $array_horarios['09:51']='591';
        $array_horarios['09:52']='592';
        $array_horarios['09:53']='593';
        $array_horarios['09:54']='594';
        $array_horarios['09:55']='595';
        $array_horarios['09:56']='596';
        $array_horarios['09:57']='597';
        $array_horarios['09:58']='598';
        $array_horarios['09:59']='599';
        $array_horarios['10:00']='600';
        $array_horarios['10:01']='601';
        $array_horarios['10:02']='602';
        $array_horarios['10:03']='603';
        $array_horarios['10:04']='604';
        $array_horarios['10:05']='605';
        $array_horarios['10:06']='606';
        $array_horarios['10:07']='607';
        $array_horarios['10:08']='608';
        $array_horarios['10:09']='609';
        $array_horarios['10:10']='610';
        $array_horarios['10:11']='611';
        $array_horarios['10:12']='612';
        $array_horarios['10:13']='613';
        $array_horarios['10:14']='614';
        $array_horarios['10:15']='615';
        $array_horarios['10:16']='616';
        $array_horarios['10:17']='617';
        $array_horarios['10:18']='618';
        $array_horarios['10:19']='619';
        $array_horarios['10:20']='620';
        $array_horarios['10:21']='621';
        $array_horarios['10:22']='622';
        $array_horarios['10:23']='623';
        $array_horarios['10:24']='624';
        $array_horarios['10:25']='625';
        $array_horarios['10:26']='626';
        $array_horarios['10:27']='627';
        $array_horarios['10:28']='628';
        $array_horarios['10:29']='629';
        $array_horarios['10:30']='630';
        $array_horarios['10:31']='631';
        $array_horarios['10:32']='632';
        $array_horarios['10:33']='633';
        $array_horarios['10:34']='634';
        $array_horarios['10:35']='635';
        $array_horarios['10:36']='636';
        $array_horarios['10:37']='637';
        $array_horarios['10:38']='638';
        $array_horarios['10:39']='639';
        $array_horarios['10:40']='640';
        $array_horarios['10:41']='641';
        $array_horarios['10:42']='642';
        $array_horarios['10:43']='643';
        $array_horarios['10:44']='644';
        $array_horarios['10:45']='645';
        $array_horarios['10:46']='646';
        $array_horarios['10:47']='647';
        $array_horarios['10:48']='648';
        $array_horarios['10:49']='649';
        $array_horarios['10:50']='650';
        $array_horarios['10:51']='651';
        $array_horarios['10:52']='652';
        $array_horarios['10:53']='653';
        $array_horarios['10:54']='654';
        $array_horarios['10:55']='655';
        $array_horarios['10:56']='656';
        $array_horarios['10:57']='657';
        $array_horarios['10:58']='658';
        $array_horarios['10:59']='659';
        $array_horarios['11:00']='660';
        $array_horarios['11:01']='661';
        $array_horarios['11:02']='662';
        $array_horarios['11:03']='663';
        $array_horarios['11:04']='664';
        $array_horarios['11:05']='665';
        $array_horarios['11:06']='666';
        $array_horarios['11:07']='667';
        $array_horarios['11:08']='668';
        $array_horarios['11:09']='669';
        $array_horarios['11:10']='670';
        $array_horarios['11:11']='671';
        $array_horarios['11:12']='672';
        $array_horarios['11:13']='673';
        $array_horarios['11:14']='674';
        $array_horarios['11:15']='675';
        $array_horarios['11:16']='676';
        $array_horarios['11:17']='677';
        $array_horarios['11:18']='678';
        $array_horarios['11:19']='679';
        $array_horarios['11:20']='680';
        $array_horarios['11:21']='681';
        $array_horarios['11:22']='682';
        $array_horarios['11:23']='683';
        $array_horarios['11:24']='684';
        $array_horarios['11:25']='685';
        $array_horarios['11:26']='686';
        $array_horarios['11:27']='687';
        $array_horarios['11:28']='688';
        $array_horarios['11:29']='689';
        $array_horarios['11:30']='690';
        $array_horarios['11:31']='691';
        $array_horarios['11:32']='692';
        $array_horarios['11:33']='693';
        $array_horarios['11:34']='694';
        $array_horarios['11:35']='695';
        $array_horarios['11:36']='696';
        $array_horarios['11:37']='697';
        $array_horarios['11:38']='698';
        $array_horarios['11:39']='699';
        $array_horarios['11:40']='700';
        $array_horarios['11:41']='701';
        $array_horarios['11:42']='702';
        $array_horarios['11:43']='703';
        $array_horarios['11:44']='704';
        $array_horarios['11:45']='705';
        $array_horarios['11:46']='706';
        $array_horarios['11:47']='707';
        $array_horarios['11:48']='708';
        $array_horarios['11:49']='709';
        $array_horarios['11:50']='710';
        $array_horarios['11:51']='711';
        $array_horarios['11:52']='712';
        $array_horarios['11:53']='713';
        $array_horarios['11:54']='714';
        $array_horarios['11:55']='715';
        $array_horarios['11:56']='716';
        $array_horarios['11:57']='717';
        $array_horarios['11:58']='718';
        $array_horarios['11:59']='719';
        $array_horarios['12:00']='720';
        $array_horarios['12:01']='721';
        $array_horarios['12:02']='722';
        $array_horarios['12:03']='723';
        $array_horarios['12:04']='724';
        $array_horarios['12:05']='725';
        $array_horarios['12:06']='726';
        $array_horarios['12:07']='727';
        $array_horarios['12:08']='728';
        $array_horarios['12:09']='729';
        $array_horarios['12:10']='730';
        $array_horarios['12:11']='731';
        $array_horarios['12:12']='732';
        $array_horarios['12:13']='733';
        $array_horarios['12:14']='734';
        $array_horarios['12:15']='735';
        $array_horarios['12:16']='736';
        $array_horarios['12:17']='737';
        $array_horarios['12:18']='738';
        $array_horarios['12:19']='739';
        $array_horarios['12:20']='740';
        $array_horarios['12:21']='741';
        $array_horarios['12:22']='742';
        $array_horarios['12:23']='743';
        $array_horarios['12:24']='744';
        $array_horarios['12:25']='745';
        $array_horarios['12:26']='746';
        $array_horarios['12:27']='747';
        $array_horarios['12:28']='748';
        $array_horarios['12:29']='749';
        $array_horarios['12:30']='750';
        $array_horarios['12:31']='751';
        $array_horarios['12:32']='752';
        $array_horarios['12:33']='753';
        $array_horarios['12:34']='754';
        $array_horarios['12:35']='755';
        $array_horarios['12:36']='756';
        $array_horarios['12:37']='757';
        $array_horarios['12:38']='758';
        $array_horarios['12:39']='759';
        $array_horarios['12:40']='760';
        $array_horarios['12:41']='761';
        $array_horarios['12:42']='762';
        $array_horarios['12:43']='763';
        $array_horarios['12:44']='764';
        $array_horarios['12:45']='765';
        $array_horarios['12:46']='766';
        $array_horarios['12:47']='767';
        $array_horarios['12:48']='768';
        $array_horarios['12:49']='769';
        $array_horarios['12:50']='770';
        $array_horarios['12:51']='771';
        $array_horarios['12:52']='772';
        $array_horarios['12:53']='773';
        $array_horarios['12:54']='774';
        $array_horarios['12:55']='775';
        $array_horarios['12:56']='776';
        $array_horarios['12:57']='777';
        $array_horarios['12:58']='778';
        $array_horarios['12:59']='779';
        $array_horarios['13:00']='780';
        $array_horarios['13:01']='781';
        $array_horarios['13:02']='782';
        $array_horarios['13:03']='783';
        $array_horarios['13:04']='784';
        $array_horarios['13:05']='785';
        $array_horarios['13:06']='786';
        $array_horarios['13:07']='787';
        $array_horarios['13:08']='788';
        $array_horarios['13:09']='789';
        $array_horarios['13:10']='790';
        $array_horarios['13:11']='791';
        $array_horarios['13:12']='792';
        $array_horarios['13:13']='793';
        $array_horarios['13:14']='794';
        $array_horarios['13:15']='795';
        $array_horarios['13:16']='796';
        $array_horarios['13:17']='797';
        $array_horarios['13:18']='798';
        $array_horarios['13:19']='799';
        $array_horarios['13:20']='800';
        $array_horarios['13:21']='801';
        $array_horarios['13:22']='802';
        $array_horarios['13:23']='803';
        $array_horarios['13:24']='804';
        $array_horarios['13:25']='805';
        $array_horarios['13:26']='806';
        $array_horarios['13:27']='807';
        $array_horarios['13:28']='808';
        $array_horarios['13:29']='809';
        $array_horarios['13:30']='810';
        $array_horarios['13:31']='811';
        $array_horarios['13:32']='812';
        $array_horarios['13:33']='813';
        $array_horarios['13:34']='814';
        $array_horarios['13:35']='815';
        $array_horarios['13:36']='816';
        $array_horarios['13:37']='817';
        $array_horarios['13:38']='818';
        $array_horarios['13:39']='819';
        $array_horarios['13:40']='820';
        $array_horarios['13:41']='821';
        $array_horarios['13:42']='822';
        $array_horarios['13:43']='823';
        $array_horarios['13:44']='824';
        $array_horarios['13:45']='825';
        $array_horarios['13:46']='826';
        $array_horarios['13:47']='827';
        $array_horarios['13:48']='828';
        $array_horarios['13:49']='829';
        $array_horarios['13:50']='830';
        $array_horarios['13:51']='831';
        $array_horarios['13:52']='832';
        $array_horarios['13:53']='833';
        $array_horarios['13:54']='834';
        $array_horarios['13:55']='835';
        $array_horarios['13:56']='836';
        $array_horarios['13:57']='837';
        $array_horarios['13:58']='838';
        $array_horarios['13:59']='839';
        $array_horarios['14:00']='840';
        $array_horarios['14:01']='841';
        $array_horarios['14:02']='842';
        $array_horarios['14:03']='843';
        $array_horarios['14:04']='844';
        $array_horarios['14:05']='845';
        $array_horarios['14:06']='846';
        $array_horarios['14:07']='847';
        $array_horarios['14:08']='848';
        $array_horarios['14:09']='849';
        $array_horarios['14:10']='850';
        $array_horarios['14:11']='851';
        $array_horarios['14:12']='852';
        $array_horarios['14:13']='853';
        $array_horarios['14:14']='854';
        $array_horarios['14:15']='855';
        $array_horarios['14:16']='856';
        $array_horarios['14:17']='857';
        $array_horarios['14:18']='858';
        $array_horarios['14:19']='859';
        $array_horarios['14:20']='860';
        $array_horarios['14:21']='861';
        $array_horarios['14:22']='862';
        $array_horarios['14:23']='863';
        $array_horarios['14:24']='864';
        $array_horarios['14:25']='865';
        $array_horarios['14:26']='866';
        $array_horarios['14:27']='867';
        $array_horarios['14:28']='868';
        $array_horarios['14:29']='869';
        $array_horarios['14:30']='870';
        $array_horarios['14:31']='871';
        $array_horarios['14:32']='872';
        $array_horarios['14:33']='873';
        $array_horarios['14:34']='874';
        $array_horarios['14:35']='875';
        $array_horarios['14:36']='876';
        $array_horarios['14:37']='877';
        $array_horarios['14:38']='878';
        $array_horarios['14:39']='879';
        $array_horarios['14:40']='880';
        $array_horarios['14:41']='881';
        $array_horarios['14:42']='882';
        $array_horarios['14:43']='883';
        $array_horarios['14:44']='884';
        $array_horarios['14:45']='885';
        $array_horarios['14:46']='886';
        $array_horarios['14:47']='887';
        $array_horarios['14:48']='888';
        $array_horarios['14:49']='889';
        $array_horarios['14:50']='890';
        $array_horarios['14:51']='891';
        $array_horarios['14:52']='892';
        $array_horarios['14:53']='893';
        $array_horarios['14:54']='894';
        $array_horarios['14:55']='895';
        $array_horarios['14:56']='896';
        $array_horarios['14:57']='897';
        $array_horarios['14:58']='898';
        $array_horarios['14:59']='899';
        $array_horarios['15:00']='900';
        $array_horarios['15:01']='901';
        $array_horarios['15:02']='902';
        $array_horarios['15:03']='903';
        $array_horarios['15:04']='904';
        $array_horarios['15:05']='905';
        $array_horarios['15:06']='906';
        $array_horarios['15:07']='907';
        $array_horarios['15:08']='908';
        $array_horarios['15:09']='909';
        $array_horarios['15:10']='910';
        $array_horarios['15:11']='911';
        $array_horarios['15:12']='912';
        $array_horarios['15:13']='913';
        $array_horarios['15:14']='914';
        $array_horarios['15:15']='915';
        $array_horarios['15:16']='916';
        $array_horarios['15:17']='917';
        $array_horarios['15:18']='918';
        $array_horarios['15:19']='919';
        $array_horarios['15:20']='920';
        $array_horarios['15:21']='921';
        $array_horarios['15:22']='922';
        $array_horarios['15:23']='923';
        $array_horarios['15:24']='924';
        $array_horarios['15:25']='925';
        $array_horarios['15:26']='926';
        $array_horarios['15:27']='927';
        $array_horarios['15:28']='928';
        $array_horarios['15:29']='929';
        $array_horarios['15:30']='930';
        $array_horarios['15:31']='931';
        $array_horarios['15:32']='932';
        $array_horarios['15:33']='933';
        $array_horarios['15:34']='934';
        $array_horarios['15:35']='935';
        $array_horarios['15:36']='936';
        $array_horarios['15:37']='937';
        $array_horarios['15:38']='938';
        $array_horarios['15:39']='939';
        $array_horarios['15:40']='940';
        $array_horarios['15:41']='941';
        $array_horarios['15:42']='942';
        $array_horarios['15:43']='943';
        $array_horarios['15:44']='944';
        $array_horarios['15:45']='945';
        $array_horarios['15:46']='946';
        $array_horarios['15:47']='947';
        $array_horarios['15:48']='948';
        $array_horarios['15:49']='949';
        $array_horarios['15:50']='950';
        $array_horarios['15:51']='951';
        $array_horarios['15:52']='952';
        $array_horarios['15:53']='953';
        $array_horarios['15:54']='954';
        $array_horarios['15:55']='955';
        $array_horarios['15:56']='956';
        $array_horarios['15:57']='957';
        $array_horarios['15:58']='958';
        $array_horarios['15:59']='959';
        $array_horarios['16:00']='960';
        $array_horarios['16:01']='961';
        $array_horarios['16:02']='962';
        $array_horarios['16:03']='963';
        $array_horarios['16:04']='964';
        $array_horarios['16:05']='965';
        $array_horarios['16:06']='966';
        $array_horarios['16:07']='967';
        $array_horarios['16:08']='968';
        $array_horarios['16:09']='969';
        $array_horarios['16:10']='970';
        $array_horarios['16:11']='971';
        $array_horarios['16:12']='972';
        $array_horarios['16:13']='973';
        $array_horarios['16:14']='974';
        $array_horarios['16:15']='975';
        $array_horarios['16:16']='976';
        $array_horarios['16:17']='977';
        $array_horarios['16:18']='978';
        $array_horarios['16:19']='979';
        $array_horarios['16:20']='980';
        $array_horarios['16:21']='981';
        $array_horarios['16:22']='982';
        $array_horarios['16:23']='983';
        $array_horarios['16:24']='984';
        $array_horarios['16:25']='985';
        $array_horarios['16:26']='986';
        $array_horarios['16:27']='987';
        $array_horarios['16:28']='988';
        $array_horarios['16:29']='989';
        $array_horarios['16:30']='990';
        $array_horarios['16:31']='991';
        $array_horarios['16:32']='992';
        $array_horarios['16:33']='993';
        $array_horarios['16:34']='994';
        $array_horarios['16:35']='995';
        $array_horarios['16:36']='996';
        $array_horarios['16:37']='997';
        $array_horarios['16:38']='998';
        $array_horarios['16:39']='999';
        $array_horarios['16:40']='1000';
        $array_horarios['16:41']='1001';
        $array_horarios['16:42']='1002';
        $array_horarios['16:43']='1003';
        $array_horarios['16:44']='1004';
        $array_horarios['16:45']='1005';
        $array_horarios['16:46']='1006';
        $array_horarios['16:47']='1007';
        $array_horarios['16:48']='1008';
        $array_horarios['16:49']='1009';
        $array_horarios['16:50']='1010';
        $array_horarios['16:51']='1011';
        $array_horarios['16:52']='1012';
        $array_horarios['16:53']='1013';
        $array_horarios['16:54']='1014';
        $array_horarios['16:55']='1015';
        $array_horarios['16:56']='1016';
        $array_horarios['16:57']='1017';
        $array_horarios['16:58']='1018';
        $array_horarios['16:59']='1019';
        $array_horarios['17:00']='1020';
        $array_horarios['17:01']='1021';
        $array_horarios['17:02']='1022';
        $array_horarios['17:03']='1023';
        $array_horarios['17:04']='1024';
        $array_horarios['17:05']='1025';
        $array_horarios['17:06']='1026';
        $array_horarios['17:07']='1027';
        $array_horarios['17:08']='1028';
        $array_horarios['17:09']='1029';
        $array_horarios['17:10']='1030';
        $array_horarios['17:11']='1031';
        $array_horarios['17:12']='1032';
        $array_horarios['17:13']='1033';
        $array_horarios['17:14']='1034';
        $array_horarios['17:15']='1035';
        $array_horarios['17:16']='1036';
        $array_horarios['17:17']='1037';
        $array_horarios['17:18']='1038';
        $array_horarios['17:19']='1039';
        $array_horarios['17:20']='1040';
        $array_horarios['17:21']='1041';
        $array_horarios['17:22']='1042';
        $array_horarios['17:23']='1043';
        $array_horarios['17:24']='1044';
        $array_horarios['17:25']='1045';
        $array_horarios['17:26']='1046';
        $array_horarios['17:27']='1047';
        $array_horarios['17:28']='1048';
        $array_horarios['17:29']='1049';
        $array_horarios['17:30']='1050';
        $array_horarios['17:31']='1051';
        $array_horarios['17:32']='1052';
        $array_horarios['17:33']='1053';
        $array_horarios['17:34']='1054';
        $array_horarios['17:35']='1055';
        $array_horarios['17:36']='1056';
        $array_horarios['17:37']='1057';
        $array_horarios['17:38']='1058';
        $array_horarios['17:39']='1059';
        $array_horarios['17:40']='1060';
        $array_horarios['17:41']='1061';
        $array_horarios['17:42']='1062';
        $array_horarios['17:43']='1063';
        $array_horarios['17:44']='1064';
        $array_horarios['17:45']='1065';
        $array_horarios['17:46']='1066';
        $array_horarios['17:47']='1067';
        $array_horarios['17:48']='1068';
        $array_horarios['17:49']='1069';
        $array_horarios['17:50']='1070';
        $array_horarios['17:51']='1071';
        $array_horarios['17:52']='1072';
        $array_horarios['17:53']='1073';
        $array_horarios['17:54']='1074';
        $array_horarios['17:55']='1075';
        $array_horarios['17:56']='1076';
        $array_horarios['17:57']='1077';
        $array_horarios['17:58']='1078';
        $array_horarios['17:59']='1079';
        $array_horarios['18:00']='1080';
        $array_horarios['18:01']='1081';
        $array_horarios['18:02']='1082';
        $array_horarios['18:03']='1083';
        $array_horarios['18:04']='1084';
        $array_horarios['18:05']='1085';
        $array_horarios['18:06']='1086';
        $array_horarios['18:07']='1087';
        $array_horarios['18:08']='1088';
        $array_horarios['18:09']='1089';
        $array_horarios['18:10']='1090';
        $array_horarios['18:11']='1091';
        $array_horarios['18:12']='1092';
        $array_horarios['18:13']='1093';
        $array_horarios['18:14']='1094';
        $array_horarios['18:15']='1095';
        $array_horarios['18:16']='1096';
        $array_horarios['18:17']='1097';
        $array_horarios['18:18']='1098';
        $array_horarios['18:19']='1099';
        $array_horarios['18:20']='1100';
        $array_horarios['18:21']='1101';
        $array_horarios['18:22']='1102';
        $array_horarios['18:23']='1103';
        $array_horarios['18:24']='1104';
        $array_horarios['18:25']='1105';
        $array_horarios['18:26']='1106';
        $array_horarios['18:27']='1107';
        $array_horarios['18:28']='1108';
        $array_horarios['18:29']='1109';
        $array_horarios['18:30']='1110';
        $array_horarios['18:31']='1111';
        $array_horarios['18:32']='1112';
        $array_horarios['18:33']='1113';
        $array_horarios['18:34']='1114';
        $array_horarios['18:35']='1115';
        $array_horarios['18:36']='1116';
        $array_horarios['18:37']='1117';
        $array_horarios['18:38']='1118';
        $array_horarios['18:39']='1119';
        $array_horarios['18:40']='1120';
        $array_horarios['18:41']='1121';
        $array_horarios['18:42']='1122';
        $array_horarios['18:43']='1123';
        $array_horarios['18:44']='1124';
        $array_horarios['18:45']='1125';
        $array_horarios['18:46']='1126';
        $array_horarios['18:47']='1127';
        $array_horarios['18:48']='1128';
        $array_horarios['18:49']='1129';
        $array_horarios['18:50']='1130';
        $array_horarios['18:51']='1131';
        $array_horarios['18:52']='1132';
        $array_horarios['18:53']='1133';
        $array_horarios['18:54']='1134';
        $array_horarios['18:55']='1135';
        $array_horarios['18:56']='1136';
        $array_horarios['18:57']='1137';
        $array_horarios['18:58']='1138';
        $array_horarios['18:59']='1139';
        $array_horarios['19:00']='1140';
        $array_horarios['19:01']='1141';
        $array_horarios['19:02']='1142';
        $array_horarios['19:03']='1143';
        $array_horarios['19:04']='1144';
        $array_horarios['19:05']='1145';
        $array_horarios['19:06']='1146';
        $array_horarios['19:07']='1147';
        $array_horarios['19:08']='1148';
        $array_horarios['19:09']='1149';
        $array_horarios['19:10']='1150';
        $array_horarios['19:11']='1151';
        $array_horarios['19:12']='1152';
        $array_horarios['19:13']='1153';
        $array_horarios['19:14']='1154';
        $array_horarios['19:15']='1155';
        $array_horarios['19:16']='1156';
        $array_horarios['19:17']='1157';
        $array_horarios['19:18']='1158';
        $array_horarios['19:19']='1159';
        $array_horarios['19:20']='1160';
        $array_horarios['19:21']='1161';
        $array_horarios['19:22']='1162';
        $array_horarios['19:23']='1163';
        $array_horarios['19:24']='1164';
        $array_horarios['19:25']='1165';
        $array_horarios['19:26']='1166';
        $array_horarios['19:27']='1167';
        $array_horarios['19:28']='1168';
        $array_horarios['19:29']='1169';
        $array_horarios['19:30']='1170';
        $array_horarios['19:31']='1171';
        $array_horarios['19:32']='1172';
        $array_horarios['19:33']='1173';
        $array_horarios['19:34']='1174';
        $array_horarios['19:35']='1175';
        $array_horarios['19:36']='1176';
        $array_horarios['19:37']='1177';
        $array_horarios['19:38']='1178';
        $array_horarios['19:39']='1179';
        $array_horarios['19:40']='1180';
        $array_horarios['19:41']='1181';
        $array_horarios['19:42']='1182';
        $array_horarios['19:43']='1183';
        $array_horarios['19:44']='1184';
        $array_horarios['19:45']='1185';
        $array_horarios['19:46']='1186';
        $array_horarios['19:47']='1187';
        $array_horarios['19:48']='1188';
        $array_horarios['19:49']='1189';
        $array_horarios['19:50']='1190';
        $array_horarios['19:51']='1191';
        $array_horarios['19:52']='1192';
        $array_horarios['19:53']='1193';
        $array_horarios['19:54']='1194';
        $array_horarios['19:55']='1195';
        $array_horarios['19:56']='1196';
        $array_horarios['19:57']='1197';
        $array_horarios['19:58']='1198';
        $array_horarios['19:59']='1199';
        $array_horarios['20:00']='1200';
        $array_horarios['20:01']='1201';
        $array_horarios['20:02']='1202';
        $array_horarios['20:03']='1203';
        $array_horarios['20:04']='1204';
        $array_horarios['20:05']='1205';
        $array_horarios['20:06']='1206';
        $array_horarios['20:07']='1207';
        $array_horarios['20:08']='1208';
        $array_horarios['20:09']='1209';
        $array_horarios['20:10']='1210';
        $array_horarios['20:11']='1211';
        $array_horarios['20:12']='1212';
        $array_horarios['20:13']='1213';
        $array_horarios['20:14']='1214';
        $array_horarios['20:15']='1215';
        $array_horarios['20:16']='1216';
        $array_horarios['20:17']='1217';
        $array_horarios['20:18']='1218';
        $array_horarios['20:19']='1219';
        $array_horarios['20:20']='1220';
        $array_horarios['20:21']='1221';
        $array_horarios['20:22']='1222';
        $array_horarios['20:23']='1223';
        $array_horarios['20:24']='1224';
        $array_horarios['20:25']='1225';
        $array_horarios['20:26']='1226';
        $array_horarios['20:27']='1227';
        $array_horarios['20:28']='1228';
        $array_horarios['20:29']='1229';
        $array_horarios['20:30']='1230';
        $array_horarios['20:31']='1231';
        $array_horarios['20:32']='1232';
        $array_horarios['20:33']='1233';
        $array_horarios['20:34']='1234';
        $array_horarios['20:35']='1235';
        $array_horarios['20:36']='1236';
        $array_horarios['20:37']='1237';
        $array_horarios['20:38']='1238';
        $array_horarios['20:39']='1239';
        $array_horarios['20:40']='1240';
        $array_horarios['20:41']='1241';
        $array_horarios['20:42']='1242';
        $array_horarios['20:43']='1243';
        $array_horarios['20:44']='1244';
        $array_horarios['20:45']='1245';
        $array_horarios['20:46']='1246';
        $array_horarios['20:47']='1247';
        $array_horarios['20:48']='1248';
        $array_horarios['20:49']='1249';
        $array_horarios['20:50']='1250';
        $array_horarios['20:51']='1251';
        $array_horarios['20:52']='1252';
        $array_horarios['20:53']='1253';
        $array_horarios['20:54']='1254';
        $array_horarios['20:55']='1255';
        $array_horarios['20:56']='1256';
        $array_horarios['20:57']='1257';
        $array_horarios['20:58']='1258';
        $array_horarios['20:59']='1259';
        $array_horarios['21:00']='1260';
        $array_horarios['21:01']='1261';
        $array_horarios['21:02']='1262';
        $array_horarios['21:03']='1263';
        $array_horarios['21:04']='1264';
        $array_horarios['21:05']='1265';
        $array_horarios['21:06']='1266';
        $array_horarios['21:07']='1267';
        $array_horarios['21:08']='1268';
        $array_horarios['21:09']='1269';
        $array_horarios['21:10']='1270';
        $array_horarios['21:11']='1271';
        $array_horarios['21:12']='1272';
        $array_horarios['21:13']='1273';
        $array_horarios['21:14']='1274';
        $array_horarios['21:15']='1275';
        $array_horarios['21:16']='1276';
        $array_horarios['21:17']='1277';
        $array_horarios['21:18']='1278';
        $array_horarios['21:19']='1279';
        $array_horarios['21:20']='1280';
        $array_horarios['21:21']='1281';
        $array_horarios['21:22']='1282';
        $array_horarios['21:23']='1283';
        $array_horarios['21:24']='1284';
        $array_horarios['21:25']='1285';
        $array_horarios['21:26']='1286';
        $array_horarios['21:27']='1287';
        $array_horarios['21:28']='1288';
        $array_horarios['21:29']='1289';
        $array_horarios['21:30']='1290';
        $array_horarios['21:31']='1291';
        $array_horarios['21:32']='1292';
        $array_horarios['21:33']='1293';
        $array_horarios['21:34']='1294';
        $array_horarios['21:35']='1295';
        $array_horarios['21:36']='1296';
        $array_horarios['21:37']='1297';
        $array_horarios['21:38']='1298';
        $array_horarios['21:39']='1299';
        $array_horarios['21:40']='1300';
        $array_horarios['21:41']='1301';
        $array_horarios['21:42']='1302';
        $array_horarios['21:43']='1303';
        $array_horarios['21:44']='1304';
        $array_horarios['21:45']='1305';
        $array_horarios['21:46']='1306';
        $array_horarios['21:47']='1307';
        $array_horarios['21:48']='1308';
        $array_horarios['21:49']='1309';
        $array_horarios['21:50']='1310';
        $array_horarios['21:51']='1311';
        $array_horarios['21:52']='1312';
        $array_horarios['21:53']='1313';
        $array_horarios['21:54']='1314';
        $array_horarios['21:55']='1315';
        $array_horarios['21:56']='1316';
        $array_horarios['21:57']='1317';
        $array_horarios['21:58']='1318';
        $array_horarios['21:59']='1319';
        $array_horarios['22:00']='1320';
        $array_horarios['22:01']='1321';
        $array_horarios['22:02']='1322';
        $array_horarios['22:03']='1323';
        $array_horarios['22:04']='1324';
        $array_horarios['22:05']='1325';
        $array_horarios['22:06']='1326';
        $array_horarios['22:07']='1327';
        $array_horarios['22:08']='1328';
        $array_horarios['22:09']='1329';
        $array_horarios['22:10']='1330';
        $array_horarios['22:11']='1331';
        $array_horarios['22:12']='1332';
        $array_horarios['22:13']='1333';
        $array_horarios['22:14']='1334';
        $array_horarios['22:15']='1335';
        $array_horarios['22:16']='1336';
        $array_horarios['22:17']='1337';
        $array_horarios['22:18']='1338';
        $array_horarios['22:19']='1339';
        $array_horarios['22:20']='1340';
        $array_horarios['22:21']='1341';
        $array_horarios['22:22']='1342';
        $array_horarios['22:23']='1343';
        $array_horarios['22:24']='1344';
        $array_horarios['22:25']='1345';
        $array_horarios['22:26']='1346';
        $array_horarios['22:27']='1347';
        $array_horarios['22:28']='1348';
        $array_horarios['22:29']='1349';
        $array_horarios['22:30']='1350';
        $array_horarios['22:31']='1351';
        $array_horarios['22:32']='1352';
        $array_horarios['22:33']='1353';
        $array_horarios['22:34']='1354';
        $array_horarios['22:35']='1355';
        $array_horarios['22:36']='1356';
        $array_horarios['22:37']='1357';
        $array_horarios['22:38']='1358';
        $array_horarios['22:39']='1359';
        $array_horarios['22:40']='1360';
        $array_horarios['22:41']='1361';
        $array_horarios['22:42']='1362';
        $array_horarios['22:43']='1363';
        $array_horarios['22:44']='1364';
        $array_horarios['22:45']='1365';
        $array_horarios['22:46']='1366';
        $array_horarios['22:47']='1367';
        $array_horarios['22:48']='1368';
        $array_horarios['22:49']='1369';
        $array_horarios['22:50']='1370';
        $array_horarios['22:51']='1371';
        $array_horarios['22:52']='1372';
        $array_horarios['22:53']='1373';
        $array_horarios['22:54']='1374';
        $array_horarios['22:55']='1375';
        $array_horarios['22:56']='1376';
        $array_horarios['22:57']='1377';
        $array_horarios['22:58']='1378';
        $array_horarios['22:59']='1379';
        $array_horarios['23:00']='1380';
        $array_horarios['23:01']='1381';
        $array_horarios['23:02']='1382';
        $array_horarios['23:03']='1383';
        $array_horarios['23:04']='1384';
        $array_horarios['23:05']='1385';
        $array_horarios['23:06']='1386';
        $array_horarios['23:07']='1387';
        $array_horarios['23:08']='1388';
        $array_horarios['23:09']='1389';
        $array_horarios['23:10']='1390';
        $array_horarios['23:11']='1391';
        $array_horarios['23:12']='1392';
        $array_horarios['23:13']='1393';
        $array_horarios['23:14']='1394';
        $array_horarios['23:15']='1395';
        $array_horarios['23:16']='1396';
        $array_horarios['23:17']='1397';
        $array_horarios['23:18']='1398';
        $array_horarios['23:19']='1399';
        $array_horarios['23:20']='1400';
        $array_horarios['23:21']='1401';
        $array_horarios['23:22']='1402';
        $array_horarios['23:23']='1403';
        $array_horarios['23:24']='1404';
        $array_horarios['23:25']='1405';
        $array_horarios['23:26']='1406';
        $array_horarios['23:27']='1407';
        $array_horarios['23:28']='1408';
        $array_horarios['23:29']='1409';
        $array_horarios['23:30']='1410';
        $array_horarios['23:31']='1411';
        $array_horarios['23:32']='1412';
        $array_horarios['23:33']='1413';
        $array_horarios['23:34']='1414';
        $array_horarios['23:35']='1415';
        $array_horarios['23:36']='1416';
        $array_horarios['23:37']='1417';
        $array_horarios['23:38']='1418';
        $array_horarios['23:39']='1419';
        $array_horarios['23:40']='1420';
        $array_horarios['23:41']='1421';
        $array_horarios['23:42']='1422';
        $array_horarios['23:43']='1423';
        $array_horarios['23:44']='1424';
        $array_horarios['23:45']='1425';
        $array_horarios['23:46']='1426';
        $array_horarios['23:47']='1427';
        $array_horarios['23:48']='1428';
        $array_horarios['23:49']='1429';
        $array_horarios['23:50']='1430';
        $array_horarios['23:51']='1431';
        $array_horarios['23:52']='1432';
        $array_horarios['23:53']='1433';
        $array_horarios['23:54']='1434';
        $array_horarios['23:55']='1435';
        $array_horarios['23:56']='1436';
        $array_horarios['23:57']='1437';
        $array_horarios['23:58']='1438';
        $array_horarios['23:59']='1439';
        $array_horarios['00:00']='1440';
        

    // Arrays recargos
        $array_horarios_recargo[1]='1';
        $array_horarios_recargo[2]='1';
        $array_horarios_recargo[3]='1';
        $array_horarios_recargo[4]='1';
        $array_horarios_recargo[5]='1';
        $array_horarios_recargo[6]='1';
        $array_horarios_recargo[7]='1';
        $array_horarios_recargo[8]='1';
        $array_horarios_recargo[9]='1';
        $array_horarios_recargo[10]='1';
        $array_horarios_recargo[11]='1';
        $array_horarios_recargo[12]='1';
        $array_horarios_recargo[13]='1';
        $array_horarios_recargo[14]='1';
        $array_horarios_recargo[15]='1';
        $array_horarios_recargo[16]='1';
        $array_horarios_recargo[17]='1';
        $array_horarios_recargo[18]='1';
        $array_horarios_recargo[19]='1';
        $array_horarios_recargo[20]='1';
        $array_horarios_recargo[21]='1';
        $array_horarios_recargo[22]='1';
        $array_horarios_recargo[23]='1';
        $array_horarios_recargo[24]='1';
        $array_horarios_recargo[25]='1';
        $array_horarios_recargo[26]='1';
        $array_horarios_recargo[27]='1';
        $array_horarios_recargo[28]='1';
        $array_horarios_recargo[29]='1';
        $array_horarios_recargo[30]='1';
        $array_horarios_recargo[31]='1';
        $array_horarios_recargo[32]='1';
        $array_horarios_recargo[33]='1';
        $array_horarios_recargo[34]='1';
        $array_horarios_recargo[35]='1';
        $array_horarios_recargo[36]='1';
        $array_horarios_recargo[37]='1';
        $array_horarios_recargo[38]='1';
        $array_horarios_recargo[39]='1';
        $array_horarios_recargo[40]='1';
        $array_horarios_recargo[41]='1';
        $array_horarios_recargo[42]='1';
        $array_horarios_recargo[43]='1';
        $array_horarios_recargo[44]='1';
        $array_horarios_recargo[45]='1';
        $array_horarios_recargo[46]='1';
        $array_horarios_recargo[47]='1';
        $array_horarios_recargo[48]='1';
        $array_horarios_recargo[49]='1';
        $array_horarios_recargo[50]='1';
        $array_horarios_recargo[51]='1';
        $array_horarios_recargo[52]='1';
        $array_horarios_recargo[53]='1';
        $array_horarios_recargo[54]='1';
        $array_horarios_recargo[55]='1';
        $array_horarios_recargo[56]='1';
        $array_horarios_recargo[57]='1';
        $array_horarios_recargo[58]='1';
        $array_horarios_recargo[59]='1';
        $array_horarios_recargo[60]='1';
        $array_horarios_recargo[61]='1';
        $array_horarios_recargo[62]='1';
        $array_horarios_recargo[63]='1';
        $array_horarios_recargo[64]='1';
        $array_horarios_recargo[65]='1';
        $array_horarios_recargo[66]='1';
        $array_horarios_recargo[67]='1';
        $array_horarios_recargo[68]='1';
        $array_horarios_recargo[69]='1';
        $array_horarios_recargo[70]='1';
        $array_horarios_recargo[71]='1';
        $array_horarios_recargo[72]='1';
        $array_horarios_recargo[73]='1';
        $array_horarios_recargo[74]='1';
        $array_horarios_recargo[75]='1';
        $array_horarios_recargo[76]='1';
        $array_horarios_recargo[77]='1';
        $array_horarios_recargo[78]='1';
        $array_horarios_recargo[79]='1';
        $array_horarios_recargo[80]='1';
        $array_horarios_recargo[81]='1';
        $array_horarios_recargo[82]='1';
        $array_horarios_recargo[83]='1';
        $array_horarios_recargo[84]='1';
        $array_horarios_recargo[85]='1';
        $array_horarios_recargo[86]='1';
        $array_horarios_recargo[87]='1';
        $array_horarios_recargo[88]='1';
        $array_horarios_recargo[89]='1';
        $array_horarios_recargo[90]='1';
        $array_horarios_recargo[91]='1';
        $array_horarios_recargo[92]='1';
        $array_horarios_recargo[93]='1';
        $array_horarios_recargo[94]='1';
        $array_horarios_recargo[95]='1';
        $array_horarios_recargo[96]='1';
        $array_horarios_recargo[97]='1';
        $array_horarios_recargo[98]='1';
        $array_horarios_recargo[99]='1';
        $array_horarios_recargo[100]='1';
        $array_horarios_recargo[101]='1';
        $array_horarios_recargo[102]='1';
        $array_horarios_recargo[103]='1';
        $array_horarios_recargo[104]='1';
        $array_horarios_recargo[105]='1';
        $array_horarios_recargo[106]='1';
        $array_horarios_recargo[107]='1';
        $array_horarios_recargo[108]='1';
        $array_horarios_recargo[109]='1';
        $array_horarios_recargo[110]='1';
        $array_horarios_recargo[111]='1';
        $array_horarios_recargo[112]='1';
        $array_horarios_recargo[113]='1';
        $array_horarios_recargo[114]='1';
        $array_horarios_recargo[115]='1';
        $array_horarios_recargo[116]='1';
        $array_horarios_recargo[117]='1';
        $array_horarios_recargo[118]='1';
        $array_horarios_recargo[119]='1';
        $array_horarios_recargo[120]='1';
        $array_horarios_recargo[121]='1';
        $array_horarios_recargo[122]='1';
        $array_horarios_recargo[123]='1';
        $array_horarios_recargo[124]='1';
        $array_horarios_recargo[125]='1';
        $array_horarios_recargo[126]='1';
        $array_horarios_recargo[127]='1';
        $array_horarios_recargo[128]='1';
        $array_horarios_recargo[129]='1';
        $array_horarios_recargo[130]='1';
        $array_horarios_recargo[131]='1';
        $array_horarios_recargo[132]='1';
        $array_horarios_recargo[133]='1';
        $array_horarios_recargo[134]='1';
        $array_horarios_recargo[135]='1';
        $array_horarios_recargo[136]='1';
        $array_horarios_recargo[137]='1';
        $array_horarios_recargo[138]='1';
        $array_horarios_recargo[139]='1';
        $array_horarios_recargo[140]='1';
        $array_horarios_recargo[141]='1';
        $array_horarios_recargo[142]='1';
        $array_horarios_recargo[143]='1';
        $array_horarios_recargo[144]='1';
        $array_horarios_recargo[145]='1';
        $array_horarios_recargo[146]='1';
        $array_horarios_recargo[147]='1';
        $array_horarios_recargo[148]='1';
        $array_horarios_recargo[149]='1';
        $array_horarios_recargo[150]='1';
        $array_horarios_recargo[151]='1';
        $array_horarios_recargo[152]='1';
        $array_horarios_recargo[153]='1';
        $array_horarios_recargo[154]='1';
        $array_horarios_recargo[155]='1';
        $array_horarios_recargo[156]='1';
        $array_horarios_recargo[157]='1';
        $array_horarios_recargo[158]='1';
        $array_horarios_recargo[159]='1';
        $array_horarios_recargo[160]='1';
        $array_horarios_recargo[161]='1';
        $array_horarios_recargo[162]='1';
        $array_horarios_recargo[163]='1';
        $array_horarios_recargo[164]='1';
        $array_horarios_recargo[165]='1';
        $array_horarios_recargo[166]='1';
        $array_horarios_recargo[167]='1';
        $array_horarios_recargo[168]='1';
        $array_horarios_recargo[169]='1';
        $array_horarios_recargo[170]='1';
        $array_horarios_recargo[171]='1';
        $array_horarios_recargo[172]='1';
        $array_horarios_recargo[173]='1';
        $array_horarios_recargo[174]='1';
        $array_horarios_recargo[175]='1';
        $array_horarios_recargo[176]='1';
        $array_horarios_recargo[177]='1';
        $array_horarios_recargo[178]='1';
        $array_horarios_recargo[179]='1';
        $array_horarios_recargo[180]='1';
        $array_horarios_recargo[181]='1';
        $array_horarios_recargo[182]='1';
        $array_horarios_recargo[183]='1';
        $array_horarios_recargo[184]='1';
        $array_horarios_recargo[185]='1';
        $array_horarios_recargo[186]='1';
        $array_horarios_recargo[187]='1';
        $array_horarios_recargo[188]='1';
        $array_horarios_recargo[189]='1';
        $array_horarios_recargo[190]='1';
        $array_horarios_recargo[191]='1';
        $array_horarios_recargo[192]='1';
        $array_horarios_recargo[193]='1';
        $array_horarios_recargo[194]='1';
        $array_horarios_recargo[195]='1';
        $array_horarios_recargo[196]='1';
        $array_horarios_recargo[197]='1';
        $array_horarios_recargo[198]='1';
        $array_horarios_recargo[199]='1';
        $array_horarios_recargo[200]='1';
        $array_horarios_recargo[201]='1';
        $array_horarios_recargo[202]='1';
        $array_horarios_recargo[203]='1';
        $array_horarios_recargo[204]='1';
        $array_horarios_recargo[205]='1';
        $array_horarios_recargo[206]='1';
        $array_horarios_recargo[207]='1';
        $array_horarios_recargo[208]='1';
        $array_horarios_recargo[209]='1';
        $array_horarios_recargo[210]='1';
        $array_horarios_recargo[211]='1';
        $array_horarios_recargo[212]='1';
        $array_horarios_recargo[213]='1';
        $array_horarios_recargo[214]='1';
        $array_horarios_recargo[215]='1';
        $array_horarios_recargo[216]='1';
        $array_horarios_recargo[217]='1';
        $array_horarios_recargo[218]='1';
        $array_horarios_recargo[219]='1';
        $array_horarios_recargo[220]='1';
        $array_horarios_recargo[221]='1';
        $array_horarios_recargo[222]='1';
        $array_horarios_recargo[223]='1';
        $array_horarios_recargo[224]='1';
        $array_horarios_recargo[225]='1';
        $array_horarios_recargo[226]='1';
        $array_horarios_recargo[227]='1';
        $array_horarios_recargo[228]='1';
        $array_horarios_recargo[229]='1';
        $array_horarios_recargo[230]='1';
        $array_horarios_recargo[231]='1';
        $array_horarios_recargo[232]='1';
        $array_horarios_recargo[233]='1';
        $array_horarios_recargo[234]='1';
        $array_horarios_recargo[235]='1';
        $array_horarios_recargo[236]='1';
        $array_horarios_recargo[237]='1';
        $array_horarios_recargo[238]='1';
        $array_horarios_recargo[239]='1';
        $array_horarios_recargo[240]='1';
        $array_horarios_recargo[241]='1';
        $array_horarios_recargo[242]='1';
        $array_horarios_recargo[243]='1';
        $array_horarios_recargo[244]='1';
        $array_horarios_recargo[245]='1';
        $array_horarios_recargo[246]='1';
        $array_horarios_recargo[247]='1';
        $array_horarios_recargo[248]='1';
        $array_horarios_recargo[249]='1';
        $array_horarios_recargo[250]='1';
        $array_horarios_recargo[251]='1';
        $array_horarios_recargo[252]='1';
        $array_horarios_recargo[253]='1';
        $array_horarios_recargo[254]='1';
        $array_horarios_recargo[255]='1';
        $array_horarios_recargo[256]='1';
        $array_horarios_recargo[257]='1';
        $array_horarios_recargo[258]='1';
        $array_horarios_recargo[259]='1';
        $array_horarios_recargo[260]='1';
        $array_horarios_recargo[261]='1';
        $array_horarios_recargo[262]='1';
        $array_horarios_recargo[263]='1';
        $array_horarios_recargo[264]='1';
        $array_horarios_recargo[265]='1';
        $array_horarios_recargo[266]='1';
        $array_horarios_recargo[267]='1';
        $array_horarios_recargo[268]='1';
        $array_horarios_recargo[269]='1';
        $array_horarios_recargo[270]='1';
        $array_horarios_recargo[271]='1';
        $array_horarios_recargo[272]='1';
        $array_horarios_recargo[273]='1';
        $array_horarios_recargo[274]='1';
        $array_horarios_recargo[275]='1';
        $array_horarios_recargo[276]='1';
        $array_horarios_recargo[277]='1';
        $array_horarios_recargo[278]='1';
        $array_horarios_recargo[279]='1';
        $array_horarios_recargo[280]='1';
        $array_horarios_recargo[281]='1';
        $array_horarios_recargo[282]='1';
        $array_horarios_recargo[283]='1';
        $array_horarios_recargo[284]='1';
        $array_horarios_recargo[285]='1';
        $array_horarios_recargo[286]='1';
        $array_horarios_recargo[287]='1';
        $array_horarios_recargo[288]='1';
        $array_horarios_recargo[289]='1';
        $array_horarios_recargo[290]='1';
        $array_horarios_recargo[291]='1';
        $array_horarios_recargo[292]='1';
        $array_horarios_recargo[293]='1';
        $array_horarios_recargo[294]='1';
        $array_horarios_recargo[295]='1';
        $array_horarios_recargo[296]='1';
        $array_horarios_recargo[297]='1';
        $array_horarios_recargo[298]='1';
        $array_horarios_recargo[299]='1';
        $array_horarios_recargo[300]='1';
        $array_horarios_recargo[301]='1';
        $array_horarios_recargo[302]='1';
        $array_horarios_recargo[303]='1';
        $array_horarios_recargo[304]='1';
        $array_horarios_recargo[305]='1';
        $array_horarios_recargo[306]='1';
        $array_horarios_recargo[307]='1';
        $array_horarios_recargo[308]='1';
        $array_horarios_recargo[309]='1';
        $array_horarios_recargo[310]='1';
        $array_horarios_recargo[311]='1';
        $array_horarios_recargo[312]='1';
        $array_horarios_recargo[313]='1';
        $array_horarios_recargo[314]='1';
        $array_horarios_recargo[315]='1';
        $array_horarios_recargo[316]='1';
        $array_horarios_recargo[317]='1';
        $array_horarios_recargo[318]='1';
        $array_horarios_recargo[319]='1';
        $array_horarios_recargo[320]='1';
        $array_horarios_recargo[321]='1';
        $array_horarios_recargo[322]='1';
        $array_horarios_recargo[323]='1';
        $array_horarios_recargo[324]='1';
        $array_horarios_recargo[325]='1';
        $array_horarios_recargo[326]='1';
        $array_horarios_recargo[327]='1';
        $array_horarios_recargo[328]='1';
        $array_horarios_recargo[329]='1';
        $array_horarios_recargo[330]='1';
        $array_horarios_recargo[331]='1';
        $array_horarios_recargo[332]='1';
        $array_horarios_recargo[333]='1';
        $array_horarios_recargo[334]='1';
        $array_horarios_recargo[335]='1';
        $array_horarios_recargo[336]='1';
        $array_horarios_recargo[337]='1';
        $array_horarios_recargo[338]='1';
        $array_horarios_recargo[339]='1';
        $array_horarios_recargo[340]='1';
        $array_horarios_recargo[341]='1';
        $array_horarios_recargo[342]='1';
        $array_horarios_recargo[343]='1';
        $array_horarios_recargo[344]='1';
        $array_horarios_recargo[345]='1';
        $array_horarios_recargo[346]='1';
        $array_horarios_recargo[347]='1';
        $array_horarios_recargo[348]='1';
        $array_horarios_recargo[349]='1';
        $array_horarios_recargo[350]='1';
        $array_horarios_recargo[351]='1';
        $array_horarios_recargo[352]='1';
        $array_horarios_recargo[353]='1';
        $array_horarios_recargo[354]='1';
        $array_horarios_recargo[355]='1';
        $array_horarios_recargo[356]='1';
        $array_horarios_recargo[357]='1';
        $array_horarios_recargo[358]='1';
        $array_horarios_recargo[359]='1';
        $array_horarios_recargo[360]='1';
        $array_horarios_recargo[361]='0';
        $array_horarios_recargo[362]='0';
        $array_horarios_recargo[363]='0';
        $array_horarios_recargo[364]='0';
        $array_horarios_recargo[365]='0';
        $array_horarios_recargo[366]='0';
        $array_horarios_recargo[367]='0';
        $array_horarios_recargo[368]='0';
        $array_horarios_recargo[369]='0';
        $array_horarios_recargo[370]='0';
        $array_horarios_recargo[371]='0';
        $array_horarios_recargo[372]='0';
        $array_horarios_recargo[373]='0';
        $array_horarios_recargo[374]='0';
        $array_horarios_recargo[375]='0';
        $array_horarios_recargo[376]='0';
        $array_horarios_recargo[377]='0';
        $array_horarios_recargo[378]='0';
        $array_horarios_recargo[379]='0';
        $array_horarios_recargo[380]='0';
        $array_horarios_recargo[381]='0';
        $array_horarios_recargo[382]='0';
        $array_horarios_recargo[383]='0';
        $array_horarios_recargo[384]='0';
        $array_horarios_recargo[385]='0';
        $array_horarios_recargo[386]='0';
        $array_horarios_recargo[387]='0';
        $array_horarios_recargo[388]='0';
        $array_horarios_recargo[389]='0';
        $array_horarios_recargo[390]='0';
        $array_horarios_recargo[391]='0';
        $array_horarios_recargo[392]='0';
        $array_horarios_recargo[393]='0';
        $array_horarios_recargo[394]='0';
        $array_horarios_recargo[395]='0';
        $array_horarios_recargo[396]='0';
        $array_horarios_recargo[397]='0';
        $array_horarios_recargo[398]='0';
        $array_horarios_recargo[399]='0';
        $array_horarios_recargo[400]='0';
        $array_horarios_recargo[401]='0';
        $array_horarios_recargo[402]='0';
        $array_horarios_recargo[403]='0';
        $array_horarios_recargo[404]='0';
        $array_horarios_recargo[405]='0';
        $array_horarios_recargo[406]='0';
        $array_horarios_recargo[407]='0';
        $array_horarios_recargo[408]='0';
        $array_horarios_recargo[409]='0';
        $array_horarios_recargo[410]='0';
        $array_horarios_recargo[411]='0';
        $array_horarios_recargo[412]='0';
        $array_horarios_recargo[413]='0';
        $array_horarios_recargo[414]='0';
        $array_horarios_recargo[415]='0';
        $array_horarios_recargo[416]='0';
        $array_horarios_recargo[417]='0';
        $array_horarios_recargo[418]='0';
        $array_horarios_recargo[419]='0';
        $array_horarios_recargo[420]='0';
        $array_horarios_recargo[421]='0';
        $array_horarios_recargo[422]='0';
        $array_horarios_recargo[423]='0';
        $array_horarios_recargo[424]='0';
        $array_horarios_recargo[425]='0';
        $array_horarios_recargo[426]='0';
        $array_horarios_recargo[427]='0';
        $array_horarios_recargo[428]='0';
        $array_horarios_recargo[429]='0';
        $array_horarios_recargo[430]='0';
        $array_horarios_recargo[431]='0';
        $array_horarios_recargo[432]='0';
        $array_horarios_recargo[433]='0';
        $array_horarios_recargo[434]='0';
        $array_horarios_recargo[435]='0';
        $array_horarios_recargo[436]='0';
        $array_horarios_recargo[437]='0';
        $array_horarios_recargo[438]='0';
        $array_horarios_recargo[439]='0';
        $array_horarios_recargo[440]='0';
        $array_horarios_recargo[441]='0';
        $array_horarios_recargo[442]='0';
        $array_horarios_recargo[443]='0';
        $array_horarios_recargo[444]='0';
        $array_horarios_recargo[445]='0';
        $array_horarios_recargo[446]='0';
        $array_horarios_recargo[447]='0';
        $array_horarios_recargo[448]='0';
        $array_horarios_recargo[449]='0';
        $array_horarios_recargo[450]='0';
        $array_horarios_recargo[451]='0';
        $array_horarios_recargo[452]='0';
        $array_horarios_recargo[453]='0';
        $array_horarios_recargo[454]='0';
        $array_horarios_recargo[455]='0';
        $array_horarios_recargo[456]='0';
        $array_horarios_recargo[457]='0';
        $array_horarios_recargo[458]='0';
        $array_horarios_recargo[459]='0';
        $array_horarios_recargo[460]='0';
        $array_horarios_recargo[461]='0';
        $array_horarios_recargo[462]='0';
        $array_horarios_recargo[463]='0';
        $array_horarios_recargo[464]='0';
        $array_horarios_recargo[465]='0';
        $array_horarios_recargo[466]='0';
        $array_horarios_recargo[467]='0';
        $array_horarios_recargo[468]='0';
        $array_horarios_recargo[469]='0';
        $array_horarios_recargo[470]='0';
        $array_horarios_recargo[471]='0';
        $array_horarios_recargo[472]='0';
        $array_horarios_recargo[473]='0';
        $array_horarios_recargo[474]='0';
        $array_horarios_recargo[475]='0';
        $array_horarios_recargo[476]='0';
        $array_horarios_recargo[477]='0';
        $array_horarios_recargo[478]='0';
        $array_horarios_recargo[479]='0';
        $array_horarios_recargo[480]='0';
        $array_horarios_recargo[481]='0';
        $array_horarios_recargo[482]='0';
        $array_horarios_recargo[483]='0';
        $array_horarios_recargo[484]='0';
        $array_horarios_recargo[485]='0';
        $array_horarios_recargo[486]='0';
        $array_horarios_recargo[487]='0';
        $array_horarios_recargo[488]='0';
        $array_horarios_recargo[489]='0';
        $array_horarios_recargo[490]='0';
        $array_horarios_recargo[491]='0';
        $array_horarios_recargo[492]='0';
        $array_horarios_recargo[493]='0';
        $array_horarios_recargo[494]='0';
        $array_horarios_recargo[495]='0';
        $array_horarios_recargo[496]='0';
        $array_horarios_recargo[497]='0';
        $array_horarios_recargo[498]='0';
        $array_horarios_recargo[499]='0';
        $array_horarios_recargo[500]='0';
        $array_horarios_recargo[501]='0';
        $array_horarios_recargo[502]='0';
        $array_horarios_recargo[503]='0';
        $array_horarios_recargo[504]='0';
        $array_horarios_recargo[505]='0';
        $array_horarios_recargo[506]='0';
        $array_horarios_recargo[507]='0';
        $array_horarios_recargo[508]='0';
        $array_horarios_recargo[509]='0';
        $array_horarios_recargo[510]='0';
        $array_horarios_recargo[511]='0';
        $array_horarios_recargo[512]='0';
        $array_horarios_recargo[513]='0';
        $array_horarios_recargo[514]='0';
        $array_horarios_recargo[515]='0';
        $array_horarios_recargo[516]='0';
        $array_horarios_recargo[517]='0';
        $array_horarios_recargo[518]='0';
        $array_horarios_recargo[519]='0';
        $array_horarios_recargo[520]='0';
        $array_horarios_recargo[521]='0';
        $array_horarios_recargo[522]='0';
        $array_horarios_recargo[523]='0';
        $array_horarios_recargo[524]='0';
        $array_horarios_recargo[525]='0';
        $array_horarios_recargo[526]='0';
        $array_horarios_recargo[527]='0';
        $array_horarios_recargo[528]='0';
        $array_horarios_recargo[529]='0';
        $array_horarios_recargo[530]='0';
        $array_horarios_recargo[531]='0';
        $array_horarios_recargo[532]='0';
        $array_horarios_recargo[533]='0';
        $array_horarios_recargo[534]='0';
        $array_horarios_recargo[535]='0';
        $array_horarios_recargo[536]='0';
        $array_horarios_recargo[537]='0';
        $array_horarios_recargo[538]='0';
        $array_horarios_recargo[539]='0';
        $array_horarios_recargo[540]='0';
        $array_horarios_recargo[541]='0';
        $array_horarios_recargo[542]='0';
        $array_horarios_recargo[543]='0';
        $array_horarios_recargo[544]='0';
        $array_horarios_recargo[545]='0';
        $array_horarios_recargo[546]='0';
        $array_horarios_recargo[547]='0';
        $array_horarios_recargo[548]='0';
        $array_horarios_recargo[549]='0';
        $array_horarios_recargo[550]='0';
        $array_horarios_recargo[551]='0';
        $array_horarios_recargo[552]='0';
        $array_horarios_recargo[553]='0';
        $array_horarios_recargo[554]='0';
        $array_horarios_recargo[555]='0';
        $array_horarios_recargo[556]='0';
        $array_horarios_recargo[557]='0';
        $array_horarios_recargo[558]='0';
        $array_horarios_recargo[559]='0';
        $array_horarios_recargo[560]='0';
        $array_horarios_recargo[561]='0';
        $array_horarios_recargo[562]='0';
        $array_horarios_recargo[563]='0';
        $array_horarios_recargo[564]='0';
        $array_horarios_recargo[565]='0';
        $array_horarios_recargo[566]='0';
        $array_horarios_recargo[567]='0';
        $array_horarios_recargo[568]='0';
        $array_horarios_recargo[569]='0';
        $array_horarios_recargo[570]='0';
        $array_horarios_recargo[571]='0';
        $array_horarios_recargo[572]='0';
        $array_horarios_recargo[573]='0';
        $array_horarios_recargo[574]='0';
        $array_horarios_recargo[575]='0';
        $array_horarios_recargo[576]='0';
        $array_horarios_recargo[577]='0';
        $array_horarios_recargo[578]='0';
        $array_horarios_recargo[579]='0';
        $array_horarios_recargo[580]='0';
        $array_horarios_recargo[581]='0';
        $array_horarios_recargo[582]='0';
        $array_horarios_recargo[583]='0';
        $array_horarios_recargo[584]='0';
        $array_horarios_recargo[585]='0';
        $array_horarios_recargo[586]='0';
        $array_horarios_recargo[587]='0';
        $array_horarios_recargo[588]='0';
        $array_horarios_recargo[589]='0';
        $array_horarios_recargo[590]='0';
        $array_horarios_recargo[591]='0';
        $array_horarios_recargo[592]='0';
        $array_horarios_recargo[593]='0';
        $array_horarios_recargo[594]='0';
        $array_horarios_recargo[595]='0';
        $array_horarios_recargo[596]='0';
        $array_horarios_recargo[597]='0';
        $array_horarios_recargo[598]='0';
        $array_horarios_recargo[599]='0';
        $array_horarios_recargo[600]='0';
        $array_horarios_recargo[601]='0';
        $array_horarios_recargo[602]='0';
        $array_horarios_recargo[603]='0';
        $array_horarios_recargo[604]='0';
        $array_horarios_recargo[605]='0';
        $array_horarios_recargo[606]='0';
        $array_horarios_recargo[607]='0';
        $array_horarios_recargo[608]='0';
        $array_horarios_recargo[609]='0';
        $array_horarios_recargo[610]='0';
        $array_horarios_recargo[611]='0';
        $array_horarios_recargo[612]='0';
        $array_horarios_recargo[613]='0';
        $array_horarios_recargo[614]='0';
        $array_horarios_recargo[615]='0';
        $array_horarios_recargo[616]='0';
        $array_horarios_recargo[617]='0';
        $array_horarios_recargo[618]='0';
        $array_horarios_recargo[619]='0';
        $array_horarios_recargo[620]='0';
        $array_horarios_recargo[621]='0';
        $array_horarios_recargo[622]='0';
        $array_horarios_recargo[623]='0';
        $array_horarios_recargo[624]='0';
        $array_horarios_recargo[625]='0';
        $array_horarios_recargo[626]='0';
        $array_horarios_recargo[627]='0';
        $array_horarios_recargo[628]='0';
        $array_horarios_recargo[629]='0';
        $array_horarios_recargo[630]='0';
        $array_horarios_recargo[631]='0';
        $array_horarios_recargo[632]='0';
        $array_horarios_recargo[633]='0';
        $array_horarios_recargo[634]='0';
        $array_horarios_recargo[635]='0';
        $array_horarios_recargo[636]='0';
        $array_horarios_recargo[637]='0';
        $array_horarios_recargo[638]='0';
        $array_horarios_recargo[639]='0';
        $array_horarios_recargo[640]='0';
        $array_horarios_recargo[641]='0';
        $array_horarios_recargo[642]='0';
        $array_horarios_recargo[643]='0';
        $array_horarios_recargo[644]='0';
        $array_horarios_recargo[645]='0';
        $array_horarios_recargo[646]='0';
        $array_horarios_recargo[647]='0';
        $array_horarios_recargo[648]='0';
        $array_horarios_recargo[649]='0';
        $array_horarios_recargo[650]='0';
        $array_horarios_recargo[651]='0';
        $array_horarios_recargo[652]='0';
        $array_horarios_recargo[653]='0';
        $array_horarios_recargo[654]='0';
        $array_horarios_recargo[655]='0';
        $array_horarios_recargo[656]='0';
        $array_horarios_recargo[657]='0';
        $array_horarios_recargo[658]='0';
        $array_horarios_recargo[659]='0';
        $array_horarios_recargo[660]='0';
        $array_horarios_recargo[661]='0';
        $array_horarios_recargo[662]='0';
        $array_horarios_recargo[663]='0';
        $array_horarios_recargo[664]='0';
        $array_horarios_recargo[665]='0';
        $array_horarios_recargo[666]='0';
        $array_horarios_recargo[667]='0';
        $array_horarios_recargo[668]='0';
        $array_horarios_recargo[669]='0';
        $array_horarios_recargo[670]='0';
        $array_horarios_recargo[671]='0';
        $array_horarios_recargo[672]='0';
        $array_horarios_recargo[673]='0';
        $array_horarios_recargo[674]='0';
        $array_horarios_recargo[675]='0';
        $array_horarios_recargo[676]='0';
        $array_horarios_recargo[677]='0';
        $array_horarios_recargo[678]='0';
        $array_horarios_recargo[679]='0';
        $array_horarios_recargo[680]='0';
        $array_horarios_recargo[681]='0';
        $array_horarios_recargo[682]='0';
        $array_horarios_recargo[683]='0';
        $array_horarios_recargo[684]='0';
        $array_horarios_recargo[685]='0';
        $array_horarios_recargo[686]='0';
        $array_horarios_recargo[687]='0';
        $array_horarios_recargo[688]='0';
        $array_horarios_recargo[689]='0';
        $array_horarios_recargo[690]='0';
        $array_horarios_recargo[691]='0';
        $array_horarios_recargo[692]='0';
        $array_horarios_recargo[693]='0';
        $array_horarios_recargo[694]='0';
        $array_horarios_recargo[695]='0';
        $array_horarios_recargo[696]='0';
        $array_horarios_recargo[697]='0';
        $array_horarios_recargo[698]='0';
        $array_horarios_recargo[699]='0';
        $array_horarios_recargo[700]='0';
        $array_horarios_recargo[701]='0';
        $array_horarios_recargo[702]='0';
        $array_horarios_recargo[703]='0';
        $array_horarios_recargo[704]='0';
        $array_horarios_recargo[705]='0';
        $array_horarios_recargo[706]='0';
        $array_horarios_recargo[707]='0';
        $array_horarios_recargo[708]='0';
        $array_horarios_recargo[709]='0';
        $array_horarios_recargo[710]='0';
        $array_horarios_recargo[711]='0';
        $array_horarios_recargo[712]='0';
        $array_horarios_recargo[713]='0';
        $array_horarios_recargo[714]='0';
        $array_horarios_recargo[715]='0';
        $array_horarios_recargo[716]='0';
        $array_horarios_recargo[717]='0';
        $array_horarios_recargo[718]='0';
        $array_horarios_recargo[719]='0';
        $array_horarios_recargo[720]='0';
        $array_horarios_recargo[721]='0';
        $array_horarios_recargo[722]='0';
        $array_horarios_recargo[723]='0';
        $array_horarios_recargo[724]='0';
        $array_horarios_recargo[725]='0';
        $array_horarios_recargo[726]='0';
        $array_horarios_recargo[727]='0';
        $array_horarios_recargo[728]='0';
        $array_horarios_recargo[729]='0';
        $array_horarios_recargo[730]='0';
        $array_horarios_recargo[731]='0';
        $array_horarios_recargo[732]='0';
        $array_horarios_recargo[733]='0';
        $array_horarios_recargo[734]='0';
        $array_horarios_recargo[735]='0';
        $array_horarios_recargo[736]='0';
        $array_horarios_recargo[737]='0';
        $array_horarios_recargo[738]='0';
        $array_horarios_recargo[739]='0';
        $array_horarios_recargo[740]='0';
        $array_horarios_recargo[741]='0';
        $array_horarios_recargo[742]='0';
        $array_horarios_recargo[743]='0';
        $array_horarios_recargo[744]='0';
        $array_horarios_recargo[745]='0';
        $array_horarios_recargo[746]='0';
        $array_horarios_recargo[747]='0';
        $array_horarios_recargo[748]='0';
        $array_horarios_recargo[749]='0';
        $array_horarios_recargo[750]='0';
        $array_horarios_recargo[751]='0';
        $array_horarios_recargo[752]='0';
        $array_horarios_recargo[753]='0';
        $array_horarios_recargo[754]='0';
        $array_horarios_recargo[755]='0';
        $array_horarios_recargo[756]='0';
        $array_horarios_recargo[757]='0';
        $array_horarios_recargo[758]='0';
        $array_horarios_recargo[759]='0';
        $array_horarios_recargo[760]='0';
        $array_horarios_recargo[761]='0';
        $array_horarios_recargo[762]='0';
        $array_horarios_recargo[763]='0';
        $array_horarios_recargo[764]='0';
        $array_horarios_recargo[765]='0';
        $array_horarios_recargo[766]='0';
        $array_horarios_recargo[767]='0';
        $array_horarios_recargo[768]='0';
        $array_horarios_recargo[769]='0';
        $array_horarios_recargo[770]='0';
        $array_horarios_recargo[771]='0';
        $array_horarios_recargo[772]='0';
        $array_horarios_recargo[773]='0';
        $array_horarios_recargo[774]='0';
        $array_horarios_recargo[775]='0';
        $array_horarios_recargo[776]='0';
        $array_horarios_recargo[777]='0';
        $array_horarios_recargo[778]='0';
        $array_horarios_recargo[779]='0';
        $array_horarios_recargo[780]='0';
        $array_horarios_recargo[781]='0';
        $array_horarios_recargo[782]='0';
        $array_horarios_recargo[783]='0';
        $array_horarios_recargo[784]='0';
        $array_horarios_recargo[785]='0';
        $array_horarios_recargo[786]='0';
        $array_horarios_recargo[787]='0';
        $array_horarios_recargo[788]='0';
        $array_horarios_recargo[789]='0';
        $array_horarios_recargo[790]='0';
        $array_horarios_recargo[791]='0';
        $array_horarios_recargo[792]='0';
        $array_horarios_recargo[793]='0';
        $array_horarios_recargo[794]='0';
        $array_horarios_recargo[795]='0';
        $array_horarios_recargo[796]='0';
        $array_horarios_recargo[797]='0';
        $array_horarios_recargo[798]='0';
        $array_horarios_recargo[799]='0';
        $array_horarios_recargo[800]='0';
        $array_horarios_recargo[801]='0';
        $array_horarios_recargo[802]='0';
        $array_horarios_recargo[803]='0';
        $array_horarios_recargo[804]='0';
        $array_horarios_recargo[805]='0';
        $array_horarios_recargo[806]='0';
        $array_horarios_recargo[807]='0';
        $array_horarios_recargo[808]='0';
        $array_horarios_recargo[809]='0';
        $array_horarios_recargo[810]='0';
        $array_horarios_recargo[811]='0';
        $array_horarios_recargo[812]='0';
        $array_horarios_recargo[813]='0';
        $array_horarios_recargo[814]='0';
        $array_horarios_recargo[815]='0';
        $array_horarios_recargo[816]='0';
        $array_horarios_recargo[817]='0';
        $array_horarios_recargo[818]='0';
        $array_horarios_recargo[819]='0';
        $array_horarios_recargo[820]='0';
        $array_horarios_recargo[821]='0';
        $array_horarios_recargo[822]='0';
        $array_horarios_recargo[823]='0';
        $array_horarios_recargo[824]='0';
        $array_horarios_recargo[825]='0';
        $array_horarios_recargo[826]='0';
        $array_horarios_recargo[827]='0';
        $array_horarios_recargo[828]='0';
        $array_horarios_recargo[829]='0';
        $array_horarios_recargo[830]='0';
        $array_horarios_recargo[831]='0';
        $array_horarios_recargo[832]='0';
        $array_horarios_recargo[833]='0';
        $array_horarios_recargo[834]='0';
        $array_horarios_recargo[835]='0';
        $array_horarios_recargo[836]='0';
        $array_horarios_recargo[837]='0';
        $array_horarios_recargo[838]='0';
        $array_horarios_recargo[839]='0';
        $array_horarios_recargo[840]='0';
        $array_horarios_recargo[841]='0';
        $array_horarios_recargo[842]='0';
        $array_horarios_recargo[843]='0';
        $array_horarios_recargo[844]='0';
        $array_horarios_recargo[845]='0';
        $array_horarios_recargo[846]='0';
        $array_horarios_recargo[847]='0';
        $array_horarios_recargo[848]='0';
        $array_horarios_recargo[849]='0';
        $array_horarios_recargo[850]='0';
        $array_horarios_recargo[851]='0';
        $array_horarios_recargo[852]='0';
        $array_horarios_recargo[853]='0';
        $array_horarios_recargo[854]='0';
        $array_horarios_recargo[855]='0';
        $array_horarios_recargo[856]='0';
        $array_horarios_recargo[857]='0';
        $array_horarios_recargo[858]='0';
        $array_horarios_recargo[859]='0';
        $array_horarios_recargo[860]='0';
        $array_horarios_recargo[861]='0';
        $array_horarios_recargo[862]='0';
        $array_horarios_recargo[863]='0';
        $array_horarios_recargo[864]='0';
        $array_horarios_recargo[865]='0';
        $array_horarios_recargo[866]='0';
        $array_horarios_recargo[867]='0';
        $array_horarios_recargo[868]='0';
        $array_horarios_recargo[869]='0';
        $array_horarios_recargo[870]='0';
        $array_horarios_recargo[871]='0';
        $array_horarios_recargo[872]='0';
        $array_horarios_recargo[873]='0';
        $array_horarios_recargo[874]='0';
        $array_horarios_recargo[875]='0';
        $array_horarios_recargo[876]='0';
        $array_horarios_recargo[877]='0';
        $array_horarios_recargo[878]='0';
        $array_horarios_recargo[879]='0';
        $array_horarios_recargo[880]='0';
        $array_horarios_recargo[881]='0';
        $array_horarios_recargo[882]='0';
        $array_horarios_recargo[883]='0';
        $array_horarios_recargo[884]='0';
        $array_horarios_recargo[885]='0';
        $array_horarios_recargo[886]='0';
        $array_horarios_recargo[887]='0';
        $array_horarios_recargo[888]='0';
        $array_horarios_recargo[889]='0';
        $array_horarios_recargo[890]='0';
        $array_horarios_recargo[891]='0';
        $array_horarios_recargo[892]='0';
        $array_horarios_recargo[893]='0';
        $array_horarios_recargo[894]='0';
        $array_horarios_recargo[895]='0';
        $array_horarios_recargo[896]='0';
        $array_horarios_recargo[897]='0';
        $array_horarios_recargo[898]='0';
        $array_horarios_recargo[899]='0';
        $array_horarios_recargo[900]='0';
        $array_horarios_recargo[901]='0';
        $array_horarios_recargo[902]='0';
        $array_horarios_recargo[903]='0';
        $array_horarios_recargo[904]='0';
        $array_horarios_recargo[905]='0';
        $array_horarios_recargo[906]='0';
        $array_horarios_recargo[907]='0';
        $array_horarios_recargo[908]='0';
        $array_horarios_recargo[909]='0';
        $array_horarios_recargo[910]='0';
        $array_horarios_recargo[911]='0';
        $array_horarios_recargo[912]='0';
        $array_horarios_recargo[913]='0';
        $array_horarios_recargo[914]='0';
        $array_horarios_recargo[915]='0';
        $array_horarios_recargo[916]='0';
        $array_horarios_recargo[917]='0';
        $array_horarios_recargo[918]='0';
        $array_horarios_recargo[919]='0';
        $array_horarios_recargo[920]='0';
        $array_horarios_recargo[921]='0';
        $array_horarios_recargo[922]='0';
        $array_horarios_recargo[923]='0';
        $array_horarios_recargo[924]='0';
        $array_horarios_recargo[925]='0';
        $array_horarios_recargo[926]='0';
        $array_horarios_recargo[927]='0';
        $array_horarios_recargo[928]='0';
        $array_horarios_recargo[929]='0';
        $array_horarios_recargo[930]='0';
        $array_horarios_recargo[931]='0';
        $array_horarios_recargo[932]='0';
        $array_horarios_recargo[933]='0';
        $array_horarios_recargo[934]='0';
        $array_horarios_recargo[935]='0';
        $array_horarios_recargo[936]='0';
        $array_horarios_recargo[937]='0';
        $array_horarios_recargo[938]='0';
        $array_horarios_recargo[939]='0';
        $array_horarios_recargo[940]='0';
        $array_horarios_recargo[941]='0';
        $array_horarios_recargo[942]='0';
        $array_horarios_recargo[943]='0';
        $array_horarios_recargo[944]='0';
        $array_horarios_recargo[945]='0';
        $array_horarios_recargo[946]='0';
        $array_horarios_recargo[947]='0';
        $array_horarios_recargo[948]='0';
        $array_horarios_recargo[949]='0';
        $array_horarios_recargo[950]='0';
        $array_horarios_recargo[951]='0';
        $array_horarios_recargo[952]='0';
        $array_horarios_recargo[953]='0';
        $array_horarios_recargo[954]='0';
        $array_horarios_recargo[955]='0';
        $array_horarios_recargo[956]='0';
        $array_horarios_recargo[957]='0';
        $array_horarios_recargo[958]='0';
        $array_horarios_recargo[959]='0';
        $array_horarios_recargo[960]='0';
        $array_horarios_recargo[961]='0';
        $array_horarios_recargo[962]='0';
        $array_horarios_recargo[963]='0';
        $array_horarios_recargo[964]='0';
        $array_horarios_recargo[965]='0';
        $array_horarios_recargo[966]='0';
        $array_horarios_recargo[967]='0';
        $array_horarios_recargo[968]='0';
        $array_horarios_recargo[969]='0';
        $array_horarios_recargo[970]='0';
        $array_horarios_recargo[971]='0';
        $array_horarios_recargo[972]='0';
        $array_horarios_recargo[973]='0';
        $array_horarios_recargo[974]='0';
        $array_horarios_recargo[975]='0';
        $array_horarios_recargo[976]='0';
        $array_horarios_recargo[977]='0';
        $array_horarios_recargo[978]='0';
        $array_horarios_recargo[979]='0';
        $array_horarios_recargo[980]='0';
        $array_horarios_recargo[981]='0';
        $array_horarios_recargo[982]='0';
        $array_horarios_recargo[983]='0';
        $array_horarios_recargo[984]='0';
        $array_horarios_recargo[985]='0';
        $array_horarios_recargo[986]='0';
        $array_horarios_recargo[987]='0';
        $array_horarios_recargo[988]='0';
        $array_horarios_recargo[989]='0';
        $array_horarios_recargo[990]='0';
        $array_horarios_recargo[991]='0';
        $array_horarios_recargo[992]='0';
        $array_horarios_recargo[993]='0';
        $array_horarios_recargo[994]='0';
        $array_horarios_recargo[995]='0';
        $array_horarios_recargo[996]='0';
        $array_horarios_recargo[997]='0';
        $array_horarios_recargo[998]='0';
        $array_horarios_recargo[999]='0';
        $array_horarios_recargo[1000]='0';
        $array_horarios_recargo[1001]='0';
        $array_horarios_recargo[1002]='0';
        $array_horarios_recargo[1003]='0';
        $array_horarios_recargo[1004]='0';
        $array_horarios_recargo[1005]='0';
        $array_horarios_recargo[1006]='0';
        $array_horarios_recargo[1007]='0';
        $array_horarios_recargo[1008]='0';
        $array_horarios_recargo[1009]='0';
        $array_horarios_recargo[1010]='0';
        $array_horarios_recargo[1011]='0';
        $array_horarios_recargo[1012]='0';
        $array_horarios_recargo[1013]='0';
        $array_horarios_recargo[1014]='0';
        $array_horarios_recargo[1015]='0';
        $array_horarios_recargo[1016]='0';
        $array_horarios_recargo[1017]='0';
        $array_horarios_recargo[1018]='0';
        $array_horarios_recargo[1019]='0';
        $array_horarios_recargo[1020]='0';
        $array_horarios_recargo[1021]='0';
        $array_horarios_recargo[1022]='0';
        $array_horarios_recargo[1023]='0';
        $array_horarios_recargo[1024]='0';
        $array_horarios_recargo[1025]='0';
        $array_horarios_recargo[1026]='0';
        $array_horarios_recargo[1027]='0';
        $array_horarios_recargo[1028]='0';
        $array_horarios_recargo[1029]='0';
        $array_horarios_recargo[1030]='0';
        $array_horarios_recargo[1031]='0';
        $array_horarios_recargo[1032]='0';
        $array_horarios_recargo[1033]='0';
        $array_horarios_recargo[1034]='0';
        $array_horarios_recargo[1035]='0';
        $array_horarios_recargo[1036]='0';
        $array_horarios_recargo[1037]='0';
        $array_horarios_recargo[1038]='0';
        $array_horarios_recargo[1039]='0';
        $array_horarios_recargo[1040]='0';
        $array_horarios_recargo[1041]='0';
        $array_horarios_recargo[1042]='0';
        $array_horarios_recargo[1043]='0';
        $array_horarios_recargo[1044]='0';
        $array_horarios_recargo[1045]='0';
        $array_horarios_recargo[1046]='0';
        $array_horarios_recargo[1047]='0';
        $array_horarios_recargo[1048]='0';
        $array_horarios_recargo[1049]='0';
        $array_horarios_recargo[1050]='0';
        $array_horarios_recargo[1051]='0';
        $array_horarios_recargo[1052]='0';
        $array_horarios_recargo[1053]='0';
        $array_horarios_recargo[1054]='0';
        $array_horarios_recargo[1055]='0';
        $array_horarios_recargo[1056]='0';
        $array_horarios_recargo[1057]='0';
        $array_horarios_recargo[1058]='0';
        $array_horarios_recargo[1059]='0';
        $array_horarios_recargo[1060]='0';
        $array_horarios_recargo[1061]='0';
        $array_horarios_recargo[1062]='0';
        $array_horarios_recargo[1063]='0';
        $array_horarios_recargo[1064]='0';
        $array_horarios_recargo[1065]='0';
        $array_horarios_recargo[1066]='0';
        $array_horarios_recargo[1067]='0';
        $array_horarios_recargo[1068]='0';
        $array_horarios_recargo[1069]='0';
        $array_horarios_recargo[1070]='0';
        $array_horarios_recargo[1071]='0';
        $array_horarios_recargo[1072]='0';
        $array_horarios_recargo[1073]='0';
        $array_horarios_recargo[1074]='0';
        $array_horarios_recargo[1075]='0';
        $array_horarios_recargo[1076]='0';
        $array_horarios_recargo[1077]='0';
        $array_horarios_recargo[1078]='0';
        $array_horarios_recargo[1079]='0';
        $array_horarios_recargo[1080]='0';
        $array_horarios_recargo[1081]='0';
        $array_horarios_recargo[1082]='0';
        $array_horarios_recargo[1083]='0';
        $array_horarios_recargo[1084]='0';
        $array_horarios_recargo[1085]='0';
        $array_horarios_recargo[1086]='0';
        $array_horarios_recargo[1087]='0';
        $array_horarios_recargo[1088]='0';
        $array_horarios_recargo[1089]='0';
        $array_horarios_recargo[1090]='0';
        $array_horarios_recargo[1091]='0';
        $array_horarios_recargo[1092]='0';
        $array_horarios_recargo[1093]='0';
        $array_horarios_recargo[1094]='0';
        $array_horarios_recargo[1095]='0';
        $array_horarios_recargo[1096]='0';
        $array_horarios_recargo[1097]='0';
        $array_horarios_recargo[1098]='0';
        $array_horarios_recargo[1099]='0';
        $array_horarios_recargo[1100]='0';
        $array_horarios_recargo[1101]='0';
        $array_horarios_recargo[1102]='0';
        $array_horarios_recargo[1103]='0';
        $array_horarios_recargo[1104]='0';
        $array_horarios_recargo[1105]='0';
        $array_horarios_recargo[1106]='0';
        $array_horarios_recargo[1107]='0';
        $array_horarios_recargo[1108]='0';
        $array_horarios_recargo[1109]='0';
        $array_horarios_recargo[1110]='0';
        $array_horarios_recargo[1111]='0';
        $array_horarios_recargo[1112]='0';
        $array_horarios_recargo[1113]='0';
        $array_horarios_recargo[1114]='0';
        $array_horarios_recargo[1115]='0';
        $array_horarios_recargo[1116]='0';
        $array_horarios_recargo[1117]='0';
        $array_horarios_recargo[1118]='0';
        $array_horarios_recargo[1119]='0';
        $array_horarios_recargo[1120]='0';
        $array_horarios_recargo[1121]='0';
        $array_horarios_recargo[1122]='0';
        $array_horarios_recargo[1123]='0';
        $array_horarios_recargo[1124]='0';
        $array_horarios_recargo[1125]='0';
        $array_horarios_recargo[1126]='0';
        $array_horarios_recargo[1127]='0';
        $array_horarios_recargo[1128]='0';
        $array_horarios_recargo[1129]='0';
        $array_horarios_recargo[1130]='0';
        $array_horarios_recargo[1131]='0';
        $array_horarios_recargo[1132]='0';
        $array_horarios_recargo[1133]='0';
        $array_horarios_recargo[1134]='0';
        $array_horarios_recargo[1135]='0';
        $array_horarios_recargo[1136]='0';
        $array_horarios_recargo[1137]='0';
        $array_horarios_recargo[1138]='0';
        $array_horarios_recargo[1139]='0';
        $array_horarios_recargo[1140]='0';
        $array_horarios_recargo[1141]='0';
        $array_horarios_recargo[1142]='0';
        $array_horarios_recargo[1143]='0';
        $array_horarios_recargo[1144]='0';
        $array_horarios_recargo[1145]='0';
        $array_horarios_recargo[1146]='0';
        $array_horarios_recargo[1147]='0';
        $array_horarios_recargo[1148]='0';
        $array_horarios_recargo[1149]='0';
        $array_horarios_recargo[1150]='0';
        $array_horarios_recargo[1151]='0';
        $array_horarios_recargo[1152]='0';
        $array_horarios_recargo[1153]='0';
        $array_horarios_recargo[1154]='0';
        $array_horarios_recargo[1155]='0';
        $array_horarios_recargo[1156]='0';
        $array_horarios_recargo[1157]='0';
        $array_horarios_recargo[1158]='0';
        $array_horarios_recargo[1159]='0';
        $array_horarios_recargo[1160]='0';
        $array_horarios_recargo[1161]='0';
        $array_horarios_recargo[1162]='0';
        $array_horarios_recargo[1163]='0';
        $array_horarios_recargo[1164]='0';
        $array_horarios_recargo[1165]='0';
        $array_horarios_recargo[1166]='0';
        $array_horarios_recargo[1167]='0';
        $array_horarios_recargo[1168]='0';
        $array_horarios_recargo[1169]='0';
        $array_horarios_recargo[1170]='0';
        $array_horarios_recargo[1171]='0';
        $array_horarios_recargo[1172]='0';
        $array_horarios_recargo[1173]='0';
        $array_horarios_recargo[1174]='0';
        $array_horarios_recargo[1175]='0';
        $array_horarios_recargo[1176]='0';
        $array_horarios_recargo[1177]='0';
        $array_horarios_recargo[1178]='0';
        $array_horarios_recargo[1179]='0';
        $array_horarios_recargo[1180]='0';
        $array_horarios_recargo[1181]='0';
        $array_horarios_recargo[1182]='0';
        $array_horarios_recargo[1183]='0';
        $array_horarios_recargo[1184]='0';
        $array_horarios_recargo[1185]='0';
        $array_horarios_recargo[1186]='0';
        $array_horarios_recargo[1187]='0';
        $array_horarios_recargo[1188]='0';
        $array_horarios_recargo[1189]='0';
        $array_horarios_recargo[1190]='0';
        $array_horarios_recargo[1191]='0';
        $array_horarios_recargo[1192]='0';
        $array_horarios_recargo[1193]='0';
        $array_horarios_recargo[1194]='0';
        $array_horarios_recargo[1195]='0';
        $array_horarios_recargo[1196]='0';
        $array_horarios_recargo[1197]='0';
        $array_horarios_recargo[1198]='0';
        $array_horarios_recargo[1199]='0';
        $array_horarios_recargo[1200]='0';
        $array_horarios_recargo[1201]='0';
        $array_horarios_recargo[1202]='0';
        $array_horarios_recargo[1203]='0';
        $array_horarios_recargo[1204]='0';
        $array_horarios_recargo[1205]='0';
        $array_horarios_recargo[1206]='0';
        $array_horarios_recargo[1207]='0';
        $array_horarios_recargo[1208]='0';
        $array_horarios_recargo[1209]='0';
        $array_horarios_recargo[1210]='0';
        $array_horarios_recargo[1211]='0';
        $array_horarios_recargo[1212]='0';
        $array_horarios_recargo[1213]='0';
        $array_horarios_recargo[1214]='0';
        $array_horarios_recargo[1215]='0';
        $array_horarios_recargo[1216]='0';
        $array_horarios_recargo[1217]='0';
        $array_horarios_recargo[1218]='0';
        $array_horarios_recargo[1219]='0';
        $array_horarios_recargo[1220]='0';
        $array_horarios_recargo[1221]='0';
        $array_horarios_recargo[1222]='0';
        $array_horarios_recargo[1223]='0';
        $array_horarios_recargo[1224]='0';
        $array_horarios_recargo[1225]='0';
        $array_horarios_recargo[1226]='0';
        $array_horarios_recargo[1227]='0';
        $array_horarios_recargo[1228]='0';
        $array_horarios_recargo[1229]='0';
        $array_horarios_recargo[1230]='0';
        $array_horarios_recargo[1231]='0';
        $array_horarios_recargo[1232]='0';
        $array_horarios_recargo[1233]='0';
        $array_horarios_recargo[1234]='0';
        $array_horarios_recargo[1235]='0';
        $array_horarios_recargo[1236]='0';
        $array_horarios_recargo[1237]='0';
        $array_horarios_recargo[1238]='0';
        $array_horarios_recargo[1239]='0';
        $array_horarios_recargo[1240]='0';
        $array_horarios_recargo[1241]='0';
        $array_horarios_recargo[1242]='0';
        $array_horarios_recargo[1243]='0';
        $array_horarios_recargo[1244]='0';
        $array_horarios_recargo[1245]='0';
        $array_horarios_recargo[1246]='0';
        $array_horarios_recargo[1247]='0';
        $array_horarios_recargo[1248]='0';
        $array_horarios_recargo[1249]='0';
        $array_horarios_recargo[1250]='0';
        $array_horarios_recargo[1251]='0';
        $array_horarios_recargo[1252]='0';
        $array_horarios_recargo[1253]='0';
        $array_horarios_recargo[1254]='0';
        $array_horarios_recargo[1255]='0';
        $array_horarios_recargo[1256]='0';
        $array_horarios_recargo[1257]='0';
        $array_horarios_recargo[1258]='0';
        $array_horarios_recargo[1259]='0';
        $array_horarios_recargo[1260]='0';
        $array_horarios_recargo[1261]='1';
        $array_horarios_recargo[1262]='1';
        $array_horarios_recargo[1263]='1';
        $array_horarios_recargo[1264]='1';
        $array_horarios_recargo[1265]='1';
        $array_horarios_recargo[1266]='1';
        $array_horarios_recargo[1267]='1';
        $array_horarios_recargo[1268]='1';
        $array_horarios_recargo[1269]='1';
        $array_horarios_recargo[1270]='1';
        $array_horarios_recargo[1271]='1';
        $array_horarios_recargo[1272]='1';
        $array_horarios_recargo[1273]='1';
        $array_horarios_recargo[1274]='1';
        $array_horarios_recargo[1275]='1';
        $array_horarios_recargo[1276]='1';
        $array_horarios_recargo[1277]='1';
        $array_horarios_recargo[1278]='1';
        $array_horarios_recargo[1279]='1';
        $array_horarios_recargo[1280]='1';
        $array_horarios_recargo[1281]='1';
        $array_horarios_recargo[1282]='1';
        $array_horarios_recargo[1283]='1';
        $array_horarios_recargo[1284]='1';
        $array_horarios_recargo[1285]='1';
        $array_horarios_recargo[1286]='1';
        $array_horarios_recargo[1287]='1';
        $array_horarios_recargo[1288]='1';
        $array_horarios_recargo[1289]='1';
        $array_horarios_recargo[1290]='1';
        $array_horarios_recargo[1291]='1';
        $array_horarios_recargo[1292]='1';
        $array_horarios_recargo[1293]='1';
        $array_horarios_recargo[1294]='1';
        $array_horarios_recargo[1295]='1';
        $array_horarios_recargo[1296]='1';
        $array_horarios_recargo[1297]='1';
        $array_horarios_recargo[1298]='1';
        $array_horarios_recargo[1299]='1';
        $array_horarios_recargo[1300]='1';
        $array_horarios_recargo[1301]='1';
        $array_horarios_recargo[1302]='1';
        $array_horarios_recargo[1303]='1';
        $array_horarios_recargo[1304]='1';
        $array_horarios_recargo[1305]='1';
        $array_horarios_recargo[1306]='1';
        $array_horarios_recargo[1307]='1';
        $array_horarios_recargo[1308]='1';
        $array_horarios_recargo[1309]='1';
        $array_horarios_recargo[1310]='1';
        $array_horarios_recargo[1311]='1';
        $array_horarios_recargo[1312]='1';
        $array_horarios_recargo[1313]='1';
        $array_horarios_recargo[1314]='1';
        $array_horarios_recargo[1315]='1';
        $array_horarios_recargo[1316]='1';
        $array_horarios_recargo[1317]='1';
        $array_horarios_recargo[1318]='1';
        $array_horarios_recargo[1319]='1';
        $array_horarios_recargo[1320]='1';
        $array_horarios_recargo[1321]='1';
        $array_horarios_recargo[1322]='1';
        $array_horarios_recargo[1323]='1';
        $array_horarios_recargo[1324]='1';
        $array_horarios_recargo[1325]='1';
        $array_horarios_recargo[1326]='1';
        $array_horarios_recargo[1327]='1';
        $array_horarios_recargo[1328]='1';
        $array_horarios_recargo[1329]='1';
        $array_horarios_recargo[1330]='1';
        $array_horarios_recargo[1331]='1';
        $array_horarios_recargo[1332]='1';
        $array_horarios_recargo[1333]='1';
        $array_horarios_recargo[1334]='1';
        $array_horarios_recargo[1335]='1';
        $array_horarios_recargo[1336]='1';
        $array_horarios_recargo[1337]='1';
        $array_horarios_recargo[1338]='1';
        $array_horarios_recargo[1339]='1';
        $array_horarios_recargo[1340]='1';
        $array_horarios_recargo[1341]='1';
        $array_horarios_recargo[1342]='1';
        $array_horarios_recargo[1343]='1';
        $array_horarios_recargo[1344]='1';
        $array_horarios_recargo[1345]='1';
        $array_horarios_recargo[1346]='1';
        $array_horarios_recargo[1347]='1';
        $array_horarios_recargo[1348]='1';
        $array_horarios_recargo[1349]='1';
        $array_horarios_recargo[1350]='1';
        $array_horarios_recargo[1351]='1';
        $array_horarios_recargo[1352]='1';
        $array_horarios_recargo[1353]='1';
        $array_horarios_recargo[1354]='1';
        $array_horarios_recargo[1355]='1';
        $array_horarios_recargo[1356]='1';
        $array_horarios_recargo[1357]='1';
        $array_horarios_recargo[1358]='1';
        $array_horarios_recargo[1359]='1';
        $array_horarios_recargo[1360]='1';
        $array_horarios_recargo[1361]='1';
        $array_horarios_recargo[1362]='1';
        $array_horarios_recargo[1363]='1';
        $array_horarios_recargo[1364]='1';
        $array_horarios_recargo[1365]='1';
        $array_horarios_recargo[1366]='1';
        $array_horarios_recargo[1367]='1';
        $array_horarios_recargo[1368]='1';
        $array_horarios_recargo[1369]='1';
        $array_horarios_recargo[1370]='1';
        $array_horarios_recargo[1371]='1';
        $array_horarios_recargo[1372]='1';
        $array_horarios_recargo[1373]='1';
        $array_horarios_recargo[1374]='1';
        $array_horarios_recargo[1375]='1';
        $array_horarios_recargo[1376]='1';
        $array_horarios_recargo[1377]='1';
        $array_horarios_recargo[1378]='1';
        $array_horarios_recargo[1379]='1';
        $array_horarios_recargo[1380]='1';
        $array_horarios_recargo[1381]='1';
        $array_horarios_recargo[1382]='1';
        $array_horarios_recargo[1383]='1';
        $array_horarios_recargo[1384]='1';
        $array_horarios_recargo[1385]='1';
        $array_horarios_recargo[1386]='1';
        $array_horarios_recargo[1387]='1';
        $array_horarios_recargo[1388]='1';
        $array_horarios_recargo[1389]='1';
        $array_horarios_recargo[1390]='1';
        $array_horarios_recargo[1391]='1';
        $array_horarios_recargo[1392]='1';
        $array_horarios_recargo[1393]='1';
        $array_horarios_recargo[1394]='1';
        $array_horarios_recargo[1395]='1';
        $array_horarios_recargo[1396]='1';
        $array_horarios_recargo[1397]='1';
        $array_horarios_recargo[1398]='1';
        $array_horarios_recargo[1399]='1';
        $array_horarios_recargo[1400]='1';
        $array_horarios_recargo[1401]='1';
        $array_horarios_recargo[1402]='1';
        $array_horarios_recargo[1403]='1';
        $array_horarios_recargo[1404]='1';
        $array_horarios_recargo[1405]='1';
        $array_horarios_recargo[1406]='1';
        $array_horarios_recargo[1407]='1';
        $array_horarios_recargo[1408]='1';
        $array_horarios_recargo[1409]='1';
        $array_horarios_recargo[1410]='1';
        $array_horarios_recargo[1411]='1';
        $array_horarios_recargo[1412]='1';
        $array_horarios_recargo[1413]='1';
        $array_horarios_recargo[1414]='1';
        $array_horarios_recargo[1415]='1';
        $array_horarios_recargo[1416]='1';
        $array_horarios_recargo[1417]='1';
        $array_horarios_recargo[1418]='1';
        $array_horarios_recargo[1419]='1';
        $array_horarios_recargo[1420]='1';
        $array_horarios_recargo[1421]='1';
        $array_horarios_recargo[1422]='1';
        $array_horarios_recargo[1423]='1';
        $array_horarios_recargo[1424]='1';
        $array_horarios_recargo[1425]='1';
        $array_horarios_recargo[1426]='1';
        $array_horarios_recargo[1427]='1';
        $array_horarios_recargo[1428]='1';
        $array_horarios_recargo[1429]='1';
        $array_horarios_recargo[1430]='1';
        $array_horarios_recargo[1431]='1';
        $array_horarios_recargo[1432]='1';
        $array_horarios_recargo[1433]='1';
        $array_horarios_recargo[1434]='1';
        $array_horarios_recargo[1435]='1';
        $array_horarios_recargo[1436]='1';
        $array_horarios_recargo[1437]='1';
        $array_horarios_recargo[1438]='1';
        $array_horarios_recargo[1439]='1';
        $array_horarios_recargo[1440]='1';


    function calculaAdherencia($turno_inicio_programado, $turno_fin_programado, $turno_inicio_realizado, $turno_fin_realizado, $duracion_programado) {
        if ($turno_inicio_realizado<$turno_inicio_programado) {
            $turno_inicio_ad=$turno_inicio_programado;
        } else {
            $turno_inicio_ad=$turno_inicio_realizado;
        }

        if ($turno_fin_realizado>$turno_fin_programado) {
            $turno_fin_ad=$turno_fin_programado;
        } else {
            $turno_fin_ad=$turno_fin_realizado;
        }

        $duracion_ad = dateDiff($turno_inicio_ad,$turno_fin_ad);
        $adherencia=$duracion_ad/$duracion_programado;
        if ($adherencia<0) {
          $adherencia=0;
        }
        
        return $adherencia;
    }

    function validaRecargos($fecha_inicio_turno, $fecha_fin_turno, $array_horarios, $array_horarios_recargo) {
        $dia_inicio=date('Y-m-d', strtotime($fecha_inicio_turno));
        $dia_fin=date('Y-m-d', strtotime($fecha_fin_turno));
        unset($array_recargos_final);
        unset($array_recargos_final_tem);
        if ($dia_fin==$dia_inicio) {
            $tipo_recargo=validarTipoTurno($dia_inicio);
            $hora_inicio=date('H:i', strtotime($fecha_inicio_turno));
            $hora_fin=date('H:i', strtotime($fecha_fin_turno));
            $pos_inicio=$array_horarios[$hora_inicio];
            $pos_fin=$array_horarios[$hora_fin]-$pos_inicio;

            // echo "Turno: ".$fecha_inicio_turno." A ".$fecha_fin_turno." | Pos: ".$array_horarios[$hora_inicio]." A ".$array_horarios[$hora_fin]."<br>";
            $array_recargos_final_tem=array_slice($array_horarios_recargo, $pos_inicio, $pos_fin, true);
            // echo "Suma: ".count($array_recargos_final_tem);
            $array_recargos_final[$dia_inicio][$tipo_recargo]['nocturno']=array_sum($array_recargos_final_tem);
            $array_recargos_final[$dia_inicio][$tipo_recargo]['diurno']=count($array_recargos_final_tem)-array_sum($array_recargos_final_tem);
        } elseif ($dia_fin>$dia_inicio) {
            $hora_inicio=date('H:i', strtotime($fecha_inicio_turno));
            $hora_fin_temp='00:00';
            $pos_inicio=$array_horarios[$hora_inicio];
            $pos_fin=$array_horarios[$hora_fin_temp]-$pos_inicio;
            $tipo_recargo=validarTipoTurno($dia_inicio);
            $array_recargos_final_tem=array_slice($array_horarios_recargo, $pos_inicio, $pos_fin, true);
            $array_recargos_final[$dia_inicio][$tipo_recargo]['nocturno']=array_sum($array_recargos_final_tem);
            $array_recargos_final[$dia_inicio][$tipo_recargo]['diurno']=count($array_recargos_final_tem)-array_sum($array_recargos_final_tem);

            //CAMBIO DE DIA
            // unset($array_recargos_final);
            $hora_inicio_temp='00:01';
            $hora_fin=date('H:i', strtotime($fecha_fin_turno));
            $pos_inicio=$array_horarios[$hora_inicio_temp]-1;
            $pos_fin=$array_horarios[$hora_fin]-$pos_inicio;
            $tipo_recargo=validarTipoTurno($dia_fin);
            // echo "Turno transdia: ".$fecha_inicio_turno." A ".$fecha_fin_turno." | Pos: ".$array_horarios[$hora_inicio_temp]." A ".$array_horarios[$hora_fin]."<br>";
            $array_recargos_final_tem=array_slice($array_horarios_recargo, $pos_inicio, $pos_fin, true);
            // echo "Suma: ".count($array_recargos_final_tem);
            $array_recargos_final[$dia_fin][$tipo_recargo]['nocturno']=array_sum($array_recargos_final_tem);
            $array_recargos_final[$dia_fin][$tipo_recargo]['diurno']=count($array_recargos_final_tem)-array_sum($array_recargos_final_tem);
        }
        return $array_recargos_final;
    }

    function formatear_fecha_grafica($fecha_validar) {
        $mes_validar=date("m", strtotime($fecha_validar))-1;
        $resultado_fecha=date("Y,", strtotime($fecha_validar)).$mes_validar.",".date("d,H,i,s", strtotime($fecha_validar));
        return $resultado_fecha;
    }

    function formatear_fecha_grafica_fin($fecha_validar_fin, $fecha_validar_inicio) {
        if ($fecha_validar_fin=="") {
            if (date("Y-m-d", strtotime($fecha_validar_inicio))==date("Y-m-d")) {
                $fecha_validar=date("Y-m-d H:i:s");
            } else {
                $fecha_validar=date("Y-m-d", strtotime($fecha_validar_inicio))." 23:59:59";
            }
        } else {
            $fecha_validar=$fecha_validar_fin;
        }

        $mes_validar=date("m", strtotime($fecha_validar))-1;
        $resultado_fecha=date("Y,", strtotime($fecha_validar)).$mes_validar.",".date("d,H,i,s", strtotime($fecha_validar));
        return $resultado_fecha;
    }

    function validar_cero($dato) { 
        if (iconv_strlen($dato)==1) {
          $dato_final="0".$dato;
        } else {
          $dato_final=$dato;
        } 
        return $dato_final; 
    }

    function eliminardobleSalto($cadena,$maximoSaltosSeguidos) {
        $lineas=explode("\n",$cadena);
        $contador=0;
        $resultado="";
        // recorremos todas las lineas
        foreach($lineas as $linea) {
            if(trim($linea)=="") {
                // Cada vez que una linea esta vacia, aumentamos la variable
                // $contador, y si excede el limite, seguimos en la siguiente
                // linea (continue)
                if(++$contador>$maximoSaltosSeguidos)
                    continue;
            } else{
                $contador=0;
            }
     
            // Guardamos la linea
            $resultado.=$linea."\n";
        }
        return $resultado;
    }

    function registro_log($enlace_db, $log_modulo = NULL, $log_tipo = NULL, $log_detalle = NULL, $array_log = NULL, $id_usuario=NULL) {
        if ($id_usuario==NULL) {
            $id_usuario=$_SESSION["usu_id"];
        }

        $consulta_string_log = "INSERT INTO `tb_administrador_log`(`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`) VALUES (?,?,?,?,?)";
        $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
        $consulta_registros_log->bind_param("sssss", $log_modulo, $log_tipo, $log_accion, $log_detalle_insert, $id_usuario);
      
        switch ($log_tipo) {
          case 'crear':
              $log_accion="Crear registro";
              $log_detalle_insert=$log_detalle;
              $consulta_registros_log->execute();
              break;

          case 'editar':
              $log_accion="Editar registro";

              if (count($array_log)>0) {
                  for ($i=0; $i < count($array_log['campo']); $i++) { 
                      if ($array_log['valor_old'][$i]!=$array_log['valor_new'][$i]) {
                          $log_detalle_insert=$log_detalle." | Item [".$array_log['campo'][$i]."] | Anterior [".$array_log['valor_old'][$i]."] | Nuevo [".$array_log['valor_new'][$i]."]";
                          $consulta_registros_log->execute();
                      }
                  }
              } else {
                  $log_detalle_insert=$log_detalle;
                  $consulta_registros_log->execute();
              }
              break;
          
          case 'eliminar':
            $log_accion="Eliminar registro";
            $log_detalle_insert=$log_detalle;
            $consulta_registros_log->execute();
            break;

          case 'inicio_sesion':
            $log_accion="Inicio de sesión";
            $log_detalle_insert=$log_detalle;
            $consulta_registros_log->execute();
            break;

          case 'notificacion':
            $log_accion="Notificación";
            $log_detalle_insert=$log_detalle;
            $consulta_registros_log->execute();
            break;

          case 'notificacion_error':
            $log_accion="Notifiación error";
            $log_detalle_insert=$log_detalle;
            $consulta_registros_log->execute();
            break;
        }
    }

    function nombre_ciudad($enlace_db, $id_ciudad = NULL) {
      $consulta_string="SELECT `ciu_codigo`, `ciu_departamento`, `ciu_municipio` FROM `tb_administrador_ciudades` WHERE `ciu_codigo`=?";

      $consulta_registros = $enlace_db->prepare($consulta_string);
      $consulta_registros->bind_param("s", $id_ciudad);
      $consulta_registros->execute();
      $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

      return $resultado_registros[0][2].", ".$resultado_registros[0][1];
    }

    function nombre_regional($enlace_db, $id_registro = NULL) {
      $consulta_string="SELECT `gere_id`, `gere_regional` FROM `tb_gestion_encuesta_regional` WHERE `gere_id`=?";
      $consulta_registros = $enlace_db->prepare($consulta_string);
      $consulta_registros->bind_param("s", $id_registro);
      $consulta_registros->execute();
      $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

      return $resultado_registros[0][1];
    }

    function nombre_centro_zonal($enlace_db, $id_registro = NULL) {
      $consulta_string="SELECT `gercz_id`, `gercz_centro_zonal` FROM `tb_gestion_encuesta_regional_czonal` WHERE `gercz_id`=?";
      $consulta_registros = $enlace_db->prepare($consulta_string);
      $consulta_registros->bind_param("s", $id_registro);
      $consulta_registros->execute();
      $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

      return $resultado_registros[0][1];
    }

    function generar_codigo($longitud_codigo) {
      $alphabeth ="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890_";
      $codigo = "";
      for($i=0;$i<$longitud_codigo;$i++){
          $codigo .= $alphabeth[rand(0,strlen($alphabeth)-1)];
      }

      return $codigo;
    }

    function validar_extension_icono ($extension) {
      //comprobar si es imagen
      if($extension=="png" || $extension=="jpeg" || $extension=="gif" || $extension=="jpg" || $extension=="bmp"){
          $icono_resultado="<span class='fas fa-file-image'></span>";
      }
      //compruebo si es audio
      elseif($extension=="mp3" || $extension=="wav" || $extension=="wma" || $extension=="ogg" || $extension=="mp4"){
          $icono_resultado="<span class='fas fa-file-audio'></span>";
      }
      //comrpuebo si son zip, rar u otros
      elseif ($extension=="zip" || $extension=="rar" || $extension=="tgz" || $extension=="tar") {
          $icono_resultado="<span class='fas fa-file-archive'></span>";
      }
      //compruebo si es un archivo de codigo
      elseif ($extension=="php" || $extension=="php3" || $extension=="html" || $extension=="css" || $extension=="py" || $extension=="java" || $extension=="js" || $extension=="sql") {
          $icono_resultado="<span class='fas fa-file-code'></span>";
      }
      //compruebo si es el archivo es de tipo pdf
      elseif ($extension=="pdf") {
          $icono_resultado="<span class='fas fa-file-pdf'></span>";
      }
       //compruebo si es el archivo es excel
      elseif ($extension=="xlsx") {
          $icono_resultado="<span class='fas fa-file-excel'></span>";
      }
       //compruebo si es el archivo es de powerpoint
      elseif ($extension=="pptx") {
          $icono_resultado="<span class='fas fa-file-powerpoint'></span>";
      }
       //compruebo si es el archivo es de word
      elseif ($extension=="docx") {
          $icono_resultado="<span class='fas fa-file-word'></span>";
      }
       //compruebo si es el archivo es de texto
      elseif ($extension=="txt") {
          $icono_resultado="<span class='fas fa-file-alt'></span>";
      }
       //compruebo si es el archivo es de video
      elseif ($extension=="avi" || $extension=="avi" || $extension=="asf" || $extension=="dvd" || $extension=="m1v" || $extension=="movie" || $extension=="mpeg" || $extension=="wn" || $extension=="wmv") {
          $icono_resultado="<span class='fas fa-file-video'></span>";
      } else {
          $icono_resultado="<span class='fas fa-file-alt'></span>";
      }

      return $icono_resultado;
    }

    function dateDiff($start, $end) {
        $start_ts = strtotime($start); 
        $end_ts = strtotime($end); 
        $diff = $end_ts - $start_ts;
        return round($diff); 
    }

    //funcion que convierte segundos en formato de horas:minutos:segundos
    function conversorSegundosHoras($tiempo_en_segundos) {
        $horas = floor($tiempo_en_segundos / 3600);
        $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
        $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);
        return $horas . 'h:' . $minutos . "m:" . $segundos."s";
        //return $horas . 'h:' . $minutos . "m";
    }

    //funcion que convierte segundos en formato de horas:minutos
    function conversorSegundosHoras_ns($tiempo_en_segundos) {
        $horas = floor($tiempo_en_segundos / 3600);
        $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
        $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);
        return $horas . 'h:' . $minutos . "m";
    }

    //funcion que convierte segundos en formato de horas:minutos solo números
    function conversorSegundosHoras_sn($tiempo_en_segundos) {
        $horas = floor($tiempo_en_segundos / 3600);
        $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
        $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);
        return validar_cero($horas).':'.validar_cero($minutos);
    }

    //funcion que convierte segundos en formato de horas:minutos:segundos solo números
    function conversorSegundosHorasMS_sn($tiempo_en_segundos) {
        $horas = floor($tiempo_en_segundos / 3600);
        $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
        $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);
        return validar_cero($horas).':'.validar_cero($minutos).':'.validar_cero($segundos);
    }

    //Función para obtener día festivo
    function validarFestivo($fecha) {
        $validarFestivo = new festivos();
        $dia_validar = date('d', strtotime($fecha));
        $mes_validar = date('m', strtotime($fecha));
        $anio_validar = date('Y', strtotime($fecha));
        $dia_semana = date('w', strtotime($fecha));
        $validarFestivo->festivos($anio_validar);
        $festivo = "";
        if ($validarFestivo->esFestivo($dia_validar, $mes_validar)||$dia_semana==0) {
            $festivo = "festivo_domingo";
        }
        return $festivo;
    }

    //Función para obtener día festivo
    function validarTipoTurno($fecha) {
        $validarFestivo = new festivos();
        $dia_validar = date('d', strtotime($fecha));
        $mes_validar = date('m', strtotime($fecha));
        $anio_validar = date('Y', strtotime($fecha));
        $dia_semana = date('w', strtotime($fecha));
        $validarFestivo->festivos($anio_validar);
        $tipo_turno = "";
        if ($dia_semana==0) {
            $tipo_turno = "domingo";
        } elseif ($validarFestivo->esFestivo($dia_validar, $mes_validar)) {
            $tipo_turno = "festivo";
        } else {
            $tipo_turno = "ordinario";
        }
        return $tipo_turno;
    }

    function esMobil() {
        $useragent=$_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
            return 1;
        } else {
            return 0;
        }
    }

    function generar_grafica_barra($enlace_db, $id_grafica = NULL, $titulo_grafica = NULL, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      
      if ($id_variable=="variable_edad") {
        $array_data=generar_data_edad($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } elseif ($id_variable=="variable_genero") {
        $array_data=generar_data_genero($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } elseif ($id_variable=="variable_motivo_atencion") {
        $array_data=generar_data_matencion($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } elseif ($id_variable=="variable_alertas") {
        $array_data=generar_data_alertas($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } else {
        $array_data=generar_data_pregunta($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      }

      $opciones_id=$array_data[$id_variable]['opciones_id'];
      $opciones_nombre=$array_data[$id_variable]['opciones_nombres'];
      $opciones_cantidad=$array_data[$id_variable]['opciones_cantidad'];

      $string_categoria="";
      for ($i=0; $i < count($opciones_id); $i++) { 
        $string_categoria.="'".$opciones_nombre[$opciones_id[$i]]."',";
        $string_cantidad.=$opciones_cantidad[$opciones_id[$i]].",";
      }

      $string_grafica="Highcharts.chart('grafica_".$id_grafica."', {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: '".$titulo_grafica."',
                    style: {
                        fontSize: '14px'
                    }
                },
                credits: {
                    enabled: false
                },
                subtitle: {
                    text: null
                },
                xAxis: {
                    categories: [".$string_categoria."]
                },
                yAxis: {
                    title: {
                        text: 'Cantidad'
                    }
                },
                legend: {
                    enabled: false,
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom'
                },
                tooltip: {
                    crosshairs: true,
                    shared: true
                },
                plotOptions: {
                    bar: {
                        marker: { enabled: false },
                        label: {
                            enabled: false
                        },
                        colorByPoint: true
                    }
                },
                series: [{
                        name: 'Cantidad',
                            data: [".$string_cantidad."]
                        }
                    ],

                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom'
                            }
                        }
                    }]
                }
            });";
      return $string_grafica;
    }

    function generar_grafica_torta($enlace_db, $id_grafica = NULL, $titulo_grafica = NULL, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      
      if ($id_variable=="variable_edad") {
        $array_data=generar_data_edad($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } elseif ($id_variable=="variable_genero") {
        $array_data=generar_data_genero($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } elseif ($id_variable=="variable_motivo_atencion") {
        $array_data=generar_data_matencion($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } elseif ($id_variable=="variable_alertas") {
        $array_data=generar_data_alertas($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } else {
        $array_data=generar_data_pregunta($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      }

      $opciones_id=$array_data[$id_variable]['opciones_id'];
      $opciones_nombre=$array_data[$id_variable]['opciones_nombres'];
      $opciones_cantidad=$array_data[$id_variable]['opciones_cantidad'];

      $string_categoria="";
      for ($i=0; $i < count($opciones_id); $i++) { 
        $string_data_series.="{
                                name: '".$opciones_nombre[$opciones_id[$i]]."',
                                y: ".$opciones_cantidad[$opciones_id[$i]].",
                            },";
      }

      $string_grafica="Highcharts.chart('grafica_".$id_grafica."', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '".$titulo_grafica."',
                    style: {
                        fontSize: '14px'
                    }
                },
                credits: {
                    enabled: false
                },
                subtitle: {
                    text: null
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b><br>Cantidad: <b>{point.y}</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            distance: 5,
                            format: '{point.percentage:.1f} %'
                        },
                        showInLegend: true
                    }
                },
                series: [{
                    name: 'Porcentaje',
                    colorByPoint: true,
                    data: [".$string_data_series."]
                }]
            });";
      return $string_grafica;
    }

    function generar_grafica_mapa($enlace_db, $id_grafica = NULL, $titulo_grafica = NULL, $id_encuesta = NULL, $id_variable = NULL, $tipo_mapa = NULL, $array_filtros = NULL) {
      if ($id_variable=="variable_mapa_radicados") {
        $string_porcentaje="";
        $array_mapa_radicado=generar_data_mapa_radicados($enlace_db, $id_encuesta, $array_filtros);
      } elseif ($id_variable=="variable_mapa_efectivas_cantidad") {
        $string_porcentaje="";
        $array_mapa_radicado=generar_data_mapa_efectiva($enlace_db, $id_encuesta, $tipo_mapa, $array_filtros);
      } elseif ($id_variable=="variable_mapa_efectivas_porcentaje") {
        $string_porcentaje="tooltip: {
                            pointFormat: '{point.name}: <b>{point.value:.2f}%</b>'
                        },";
        $array_mapa_radicado=generar_data_mapa_efectiva($enlace_db, $id_encuesta, $tipo_mapa, $array_filtros);
      }

      $array_mapa_radicado_co_sa = ($array_mapa_radicado['co-sa']>0) ? $array_mapa_radicado['co-sa'] : null;
      $array_mapa_radicado_co_ca = ($array_mapa_radicado['co-ca']>0) ? $array_mapa_radicado['co-ca'] : null;
      $array_mapa_radicado_co_na = ($array_mapa_radicado['co-na']>0) ? $array_mapa_radicado['co-na'] : null;
      $array_mapa_radicado_co_ch = ($array_mapa_radicado['co-ch']>0) ? $array_mapa_radicado['co-ch'] : null;
      $array_mapa_radicado_co_to = ($array_mapa_radicado['co-to']>0) ? $array_mapa_radicado['co-to'] : null;
      $array_mapa_radicado_co_cq = ($array_mapa_radicado['co-cq']>0) ? $array_mapa_radicado['co-cq'] : null;
      $array_mapa_radicado_co_hu = ($array_mapa_radicado['co-hu']>0) ? $array_mapa_radicado['co-hu'] : null;
      $array_mapa_radicado_co_pu = ($array_mapa_radicado['co-pu']>0) ? $array_mapa_radicado['co-pu'] : null;
      $array_mapa_radicado_co_am = ($array_mapa_radicado['co-am']>0) ? $array_mapa_radicado['co-am'] : null;
      $array_mapa_radicado_co_bl = ($array_mapa_radicado['co-bl']>0) ? $array_mapa_radicado['co-bl'] : null;
      $array_mapa_radicado_co_vc = ($array_mapa_radicado['co-vc']>0) ? $array_mapa_radicado['co-vc'] : null;
      $array_mapa_radicado_co_su = ($array_mapa_radicado['co-su']>0) ? $array_mapa_radicado['co-su'] : null;
      $array_mapa_radicado_co_at = ($array_mapa_radicado['co-at']>0) ? $array_mapa_radicado['co-at'] : null;
      $array_mapa_radicado_co_ce = ($array_mapa_radicado['co-ce']>0) ? $array_mapa_radicado['co-ce'] : null;
      $array_mapa_radicado_co_lg = ($array_mapa_radicado['co-lg']>0) ? $array_mapa_radicado['co-lg'] : null;
      $array_mapa_radicado_co_ma = ($array_mapa_radicado['co-ma']>0) ? $array_mapa_radicado['co-ma'] : null;
      $array_mapa_radicado_co_ar = ($array_mapa_radicado['co-ar']>0) ? $array_mapa_radicado['co-ar'] : null;
      $array_mapa_radicado_co_ns = ($array_mapa_radicado['co-ns']>0) ? $array_mapa_radicado['co-ns'] : null;
      $array_mapa_radicado_co_cs = ($array_mapa_radicado['co-cs']>0) ? $array_mapa_radicado['co-cs'] : null;
      $array_mapa_radicado_co_gv = ($array_mapa_radicado['co-gv']>0) ? $array_mapa_radicado['co-gv'] : null;
      $array_mapa_radicado_co_me = ($array_mapa_radicado['co-me']>0) ? $array_mapa_radicado['co-me'] : null;
      $array_mapa_radicado_co_vp = ($array_mapa_radicado['co-vp']>0) ? $array_mapa_radicado['co-vp'] : null;
      $array_mapa_radicado_co_vd = ($array_mapa_radicado['co-vd']>0) ? $array_mapa_radicado['co-vd'] : null;
      $array_mapa_radicado_co_an = ($array_mapa_radicado['co-an']>0) ? $array_mapa_radicado['co-an'] : null;
      $array_mapa_radicado_co_co = ($array_mapa_radicado['co-co']>0) ? $array_mapa_radicado['co-co'] : null;
      $array_mapa_radicado_co_by = ($array_mapa_radicado['co-by']>0) ? $array_mapa_radicado['co-by'] : null;
      $array_mapa_radicado_co_st = ($array_mapa_radicado['co-st']>0) ? $array_mapa_radicado['co-st'] : null;
      $array_mapa_radicado_co_cl = ($array_mapa_radicado['co-cl']>0) ? $array_mapa_radicado['co-cl'] : null;
      $array_mapa_radicado_co_cu = ($array_mapa_radicado['co-cu']>0) ? $array_mapa_radicado['co-cu'] : null;
      $array_mapa_radicado_co_1136 = ($array_mapa_radicado['co-1136']>0) ? $array_mapa_radicado['co-1136'] : null;
      $array_mapa_radicado_co_ri = ($array_mapa_radicado['co-ri']>0) ? $array_mapa_radicado['co-ri'] : null;
      $array_mapa_radicado_co_qd = ($array_mapa_radicado['co-qd']>0) ? $array_mapa_radicado['co-qd'] : null;
      $array_mapa_radicado_co_gn = ($array_mapa_radicado['co-gn']>0) ? $array_mapa_radicado['co-gn'] : null;

      $string_grafica="var data_radicados = [
            ['co-sa', ".$array_mapa_radicado_co_sa."],
            ['co-ca', ".$array_mapa_radicado_co_ca."],
            ['co-na', ".$array_mapa_radicado_co_na."],
            ['co-ch', ".$array_mapa_radicado_co_ch."],
            ['co-to', ".$array_mapa_radicado_co_to."],
            ['co-cq', ".$array_mapa_radicado_co_cq."],
            ['co-hu', ".$array_mapa_radicado_co_hu."],
            ['co-pu', ".$array_mapa_radicado_co_pu."],
            ['co-am', ".$array_mapa_radicado_co_am."],
            ['co-bl', ".$array_mapa_radicado_co_bl."],
            ['co-vc', ".$array_mapa_radicado_co_vc."],
            ['co-su', ".$array_mapa_radicado_co_su."],
            ['co-at', ".$array_mapa_radicado_co_at."],
            ['co-ce', ".$array_mapa_radicado_co_ce."],
            ['co-lg', ".$array_mapa_radicado_co_lg."],
            ['co-ma', ".$array_mapa_radicado_co_ma."],
            ['co-ar', ".$array_mapa_radicado_co_ar."],
            ['co-ns', ".$array_mapa_radicado_co_ns."],
            ['co-cs', ".$array_mapa_radicado_co_cs."],
            ['co-gv', ".$array_mapa_radicado_co_gv."],
            ['co-me', ".$array_mapa_radicado_co_me."],
            ['co-vp', ".$array_mapa_radicado_co_vp."],
            ['co-vd', ".$array_mapa_radicado_co_vd."],
            ['co-an', ".$array_mapa_radicado_co_an."],
            ['co-co', ".$array_mapa_radicado_co_co."],
            ['co-by', ".$array_mapa_radicado_co_by."],
            ['co-st', ".$array_mapa_radicado_co_st."],
            ['co-cl', ".$array_mapa_radicado_co_cl."],
            ['co-cu', ".$array_mapa_radicado_co_cu."],
            ['co-1136', ".$array_mapa_radicado_co_1136."],
            ['co-ri', ".$array_mapa_radicado_co_ri."],
            ['co-qd', ".$array_mapa_radicado_co_qd."],
            ['co-gn', ".$array_mapa_radicado_co_gn."]
        ];

        // Create the chart
        Highcharts.mapChart('grafica_".$id_grafica."', {
            chart: {
                map: 'countries/co/co-all',
            },
            title: {
                text: '".$titulo_grafica."',
                style: {
                    fontSize: '14px'
                }
            },
            subtitle: {
                text: null
            },
            credits: {
                 enabled: false
            },
            mapNavigation: {
                enabled: true,
                buttonOptions: {
                    verticalAlign: 'bottom'
                }
            },
            ".$string_porcentaje."
            colorAxis: {
                min: 1,
                max: 1000,
                type: 'logarithmic',
                minColor: '#CCE8CD',
                maxColor: '#4CAF50',
                lineWidth: 0
            },
            series: [{
                data: data_radicados,
                name: 'Regional',
                states: {
                    hover: {
                        color: '#BADA55'
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'normal',
                        fontSize: '10px',
                    },
                    format: '{point.name}'
                }
            }]
        });";
      return $string_grafica;
    }

    function generar_grafica_indicador_gral($enlace_db, $id_grafica = NULL, $titulo_grafica = NULL, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      
      $array_data=generar_data_indicador_gral($enlace_db, $id_encuesta, $id_variable, $array_filtros);

      $string_grafica="var names = ['Contactabilidad', 'Efectividad', 'Marcación'];
                var data = [".number_format($array_data['contactabilidad'], 2, '.', '').", ".number_format($array_data['efectividad'], 2, '.', '').", ".number_format($array_data['marcacion'], 2, '.', '')."];
                var dataSet = anychart.data.set(data);
                var palette = anychart.palettes.distinctColors().items(['#8BCC8D', '#4CAF50', '#4CB083', '#ffd54f', '#455a64', '#96a6a6', '#dd2c00', '#00838f', '#00bfa5', '#ffa000']);

                var makeBarWithBar = function (gauge, radius, i, width, without_stroke) {
                    var stroke = '1 #e5e4e4';
                    if (without_stroke) {
                        stroke = null;
                        gauge.label(i)
                                .text('<span>' + names[i] + ', ' + data[i] + '%</span>')// color: #7c868e
                                .useHtml(true)
                                .fontSize(11);
                        gauge.label(i)
                                .hAlign('center')
                                .vAlign('middle')
                                .anchor('right-center')
                                .padding(0, 10)
                                .height(width / 1 + '%')
                                .offsetY(radius + '%')
                                .offsetX(0);
                    }

                    gauge.bar(i).dataIndex(i)
                            .radius(radius)
                            .width(width)
                            .fill(palette.itemAt(i))
                            .stroke(null)
                            .zIndex(5);
                    gauge.bar(i + 100).dataIndex(5)
                            .radius(radius)
                            .width(width)
                            .fill('#F5F4F4')
                            .stroke(stroke)
                            .zIndex(4);

                    return gauge.bar(i)
                };

                anychart.onDocumentReady(function () {
                    var gauge = anychart.gauges.circular();
                    gauge.data(dataSet);
                    gauge.fill('#fff')
                            .stroke(null)
                            .padding(0)
                            .margin(100)
                            .startAngle(0)
                            .sweepAngle(270);

                    var axis = gauge.axis().radius(100).width(1).fill(null);
                    axis.scale()
                            .minimum(0)
                            .maximum(100)
                            .ticks({interval: 1})
                            .minorTicks({interval: 1});
                    axis.labels().enabled(false);
                    axis.ticks().enabled(false);
                    axis.minorTicks().enabled(false);
                    makeBarWithBar(gauge, 100, 0, 17, true);
                    makeBarWithBar(gauge, 80, 1, 17, true);
                    makeBarWithBar(gauge, 60, 2, 17, true);

                    gauge.margin(0);
                    gauge.title().text('<span>INDICADOR GENERAL</span>').useHtml(true);
                    gauge.title()
                            .enabled(true)
                            .hAlign('center')
                            .padding(0)
                            .margin([5, 0, 20, 0]);

                    gauge.container('grafica_".$id_grafica."');
                    gauge.draw();
                });";
      return $string_grafica;
    }

    function generar_grafica_indicador_agente($enlace_db, $id_grafica = NULL, $titulo_grafica = NULL, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      
      $array_data=generar_data_indicador_agente($enlace_db, $id_encuesta, $id_variable, $array_filtros);

      $agentes=$array_data['agentes'];
      $agentes_nombres=$array_data['agentes_nombres'];
      $data=$array_data['data'];

      $string_categoria="";
      $string_cantidad_pendiente="";
      $string_cantidad_cerrado="";
      $string_cantidad_efectivo="";
      for ($i=0; $i < count($agentes); $i++) { 
        $string_categoria.="'".$agentes_nombres[$agentes[$i]]."',";
        $string_cantidad_pendiente.=$data[$agentes[$i]]['Pendiente'].",";
        $string_cantidad_cerrado.=$data[$agentes[$i]]['Cerrado'].",";
        $string_cantidad_efectivo.=$data[$agentes[$i]]['Efectivo'].",";
      }

      $string_grafica="Highcharts.chart('grafica_".$id_grafica."', {
                            chart: {
                                type: 'bar'
                            },
                            title: {
                                text: 'INDICADOR GENERAL POR AGENTE',
                                style: {
                                    fontSize: '14px'
                                }
                            },
                            subtitle: {
                                text: null
                            },
                            colors: ['#FF0000', '#2E2E2E', '#4CAF50'],
                            xAxis: {
                                categories: [".$string_categoria."],
                                title: {
                                    text: null
                                }
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Cantidad',
                                    align: 'high'
                                },
                                labels: {
                                    overflow: 'justify',
                                    style: {
                                        fontSize: '8px'
                                    }
                                }
                            },
                            tooltip: {
                                valueSuffix: ' Radicados'
                            },
                            plotOptions: {
                                bar: {
                                    dataLabels: {
                                        enabled: true,
                                        style: {
                                            fontSize: '8px',
                                            fontWeight: 'normal'
                                        }
                                    }
                                }
                            },
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom',
                                x: 0,
                                y: 10,
                                floating: false,
                                borderWidth: 0,
                                backgroundColor:
                                    Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
                                shadow: false
                            },
                            credits: {
                                enabled: false
                            },
                            series: [{
                                name: 'Pendientes',
                                data: [".$string_cantidad_pendiente."]
                            }, {
                                name: 'Cerrados',
                                data: [".$string_cantidad_cerrado."]
                            }, {
                                name: 'Efectivas',
                                data: [".$string_cantidad_efectivo."]
                            }],
                            responsive: {
                                rules: [{
                                    condition: {
                                        maxWidth: 500
                                    },
                                    chartOptions: {
                                        legend: {
                                            layout: 'horizontal',
                                            align: 'center',
                                            verticalAlign: 'bottom'
                                        }
                                    }
                                }]
                            }
                        });";
      return $string_grafica;
    }

    function generar_grafica_indicador_agente_pro($enlace_db, $id_grafica = NULL, $titulo_grafica = NULL, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL, $estado = NULL) {
      
      if ($estado=="Gestionadas") {
        $array_data=generar_data_indicador_geagente($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } elseif ($estado=="Contactadas") {
        $array_data="";
        $array_data=generar_data_indicador_coagente($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      } elseif ($estado=="Efectivas") {
        $array_data="";
        $array_data=generar_data_indicador_efagente($enlace_db, $id_encuesta, $id_variable, $array_filtros);
      }

      $agentes=$array_data['agentes'];
      $agentes_nombres=$array_data['agentes_nombres'];
      $data=$array_data['data'];

      $string_categoria="";
      $string_cantidad="";
      for ($i=0; $i < count($agentes); $i++) { 
        $string_categoria.="'".$agentes_nombres[$agentes[$i]]."',";
        $string_cantidad.=$data[$agentes[$i]][$estado].",";
      }

      $string_grafica="Highcharts.chart('grafica_".$id_grafica."', {
                            chart: {
                                type: 'bar'
                            },
                            title: {
                                text: '".$titulo_grafica."',
                                style: {
                                    fontSize: '14px'
                                }
                            },
                            subtitle: {
                                text: null
                            },
                            colors: ['#4CAF50', '#2E2E2E', '#FF0000'],
                            xAxis: {
                                categories: [".$string_categoria."],
                                title: {
                                    text: null
                                }
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Cantidad',
                                    align: 'high'
                                },
                                labels: {
                                    overflow: 'justify',
                                    style: {
                                        fontSize: '8px'
                                    }
                                }
                            },
                            tooltip: {
                                valueSuffix: ' Radicados'
                            },
                            plotOptions: {
                                bar: {
                                    dataLabels: {
                                        enabled: true,
                                        style: {
                                            fontSize: '8px',
                                            fontWeight: 'normal'
                                        }
                                    }
                                }
                            },
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom',
                                x: 0,
                                y: 10,
                                floating: false,
                                borderWidth: 0,
                                backgroundColor:
                                    Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
                                shadow: false
                            },
                            credits: {
                                enabled: false
                            },
                            series: [{
                                name: '".$estado."',
                                data: [".$string_cantidad."]
                            }],
                            responsive: {
                                rules: [{
                                    condition: {
                                        maxWidth: 500
                                    },
                                    chartOptions: {
                                        legend: {
                                            layout: 'horizontal',
                                            align: 'center',
                                            verticalAlign: 'bottom'
                                        }
                                    }
                                }]
                            }
                        });";
      return $string_grafica;
    }

    function generar_data_pregunta($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $consulta_opciones = $enlace_db->prepare("SELECT `gemo_id`, `gemo_encuesta`, `gemo_seccion`, `gemo_pregunta`, `gemo_opcion_nombre`, `gemo_opcion_siguiente_seccion`, `gemo_orden_mostrar`, TP.`gemp_pregunta_nombre` FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta` AS TP ON `tb_gestion_encuestas_matriz_seccion_pregunta_opcion`.`gemo_pregunta`=TP.`gemp_id` WHERE `gemo_encuesta`=? AND `gemo_pregunta`=? ORDER BY `gemo_orden_mostrar` ASC");
      $consulta_opciones->bind_param("ss", $id_encuesta, $id_variable);
      $consulta_opciones->execute();
      $resultado_opciones = $consulta_opciones->get_result()->fetch_all(MYSQLI_NUM);
      
      for ($i=0; $i < count($resultado_opciones); $i++) {
          $array_grupos_graficas_data[$resultado_opciones[$i][3]]['nombre']=$resultado_opciones[$i][7];
          $array_grupos_graficas_data[$resultado_opciones[$i][3]]['opciones_id'][]=$resultado_opciones[$i][0];
          $array_grupos_graficas_data[$resultado_opciones[$i][3]]['opciones_cantidad'][$resultado_opciones[$i][0]]=0;
          $array_grupos_graficas_data[$resultado_opciones[$i][3]]['opciones_nombres'][$resultado_opciones[$i][0]]=$resultado_opciones[$i][4];
      }
      $data_consulta=array();
      array_push($data_consulta, $id_variable);
      array_push($data_consulta, $id_encuesta);


      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(TRR.`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="TRR.`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="TRR.`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }
      
      $consulta_registros_encuesta_data = $enlace_db->prepare("SELECT `gerd_pregunta`, TP.`gemp_pregunta_nombre`, `gerd_respuesta`, TPO.`gemo_opcion_nombre`, COUNT(`gerd_encuesta`) FROM `tb_gestion_encuesta_registro_data` LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta` AS TP ON `tb_gestion_encuesta_registro_data`.`gerd_pregunta`=TP.`gemp_id` LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` AS TPO ON `tb_gestion_encuesta_registro_data`.`gerd_respuesta`=TPO.`gemo_id` WHERE (TP.`gemp_tipo`='Varias opciones' OR TP.`gemp_tipo`='Casillas' OR TP.`gemp_tipo`='Desplegable') AND `gerd_pregunta`=? AND `gerd_encuesta` IN (SELECT TEN.`ger_consecutivo` FROM (SELECT `ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, MAX(`ger_registro_fecha`) FROM `tb_gestion_encuesta_registro` LEFT JOIN `tb_gestion_encuesta_radicado` AS TRR ON `tb_gestion_encuesta_registro`.`ger_radicado`=TRR.`gera_radicado` WHERE `ger_encuesta_id`=? ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY `ger_encuesta_id`, `ger_radicado`) AS TEN) GROUP BY `gerd_pregunta`, `gerd_respuesta`");
      $consulta_registros_encuesta_data->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_encuesta_data->execute();
      $resultado_registros_encuesta_data = $consulta_registros_encuesta_data->get_result()->fetch_all(MYSQLI_NUM);

      for ($i=0; $i < count($resultado_registros_encuesta_data); $i++) { 
          $array_grupos_graficas_data[$resultado_registros_encuesta_data[$i][0]]['opciones_cantidad'][$resultado_registros_encuesta_data[$i][2]]+=$resultado_registros_encuesta_data[$i][4];
      }

      return $array_grupos_graficas_data;
    }

    function generar_data_mapa_radicados($enlace_db, $id_encuesta = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_string_mapa_radicado="SELECT TR.`gere_id_mapa`, COUNT(`gera_radicado`) FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_encuesta_radicado`.`gera_regional`=TR.`gere_id` WHERE `gera_matriz_id`=? ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY TR.`gere_id_mapa`";
      $consulta_registros_mapa_radicado = $enlace_db->prepare($consulta_string_mapa_radicado);
      $consulta_registros_mapa_radicado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_mapa_radicado->execute();
      $resultado_registros_mapa_radicado = $consulta_registros_mapa_radicado->get_result()->fetch_all(MYSQLI_NUM);

      for ($i=0; $i < count($resultado_registros_mapa_radicado); $i++) { 
          $array_mapa_radicado[$resultado_registros_mapa_radicado[$i][0]]=$resultado_registros_mapa_radicado[$i][1];
      }

      return $array_mapa_radicado;
    }

    function generar_data_mapa_efectiva($enlace_db, $id_encuesta = NULL, $tipo_mapa = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_string_mapa_radicado="SELECT TR.`gere_id_mapa`, COUNT(`gera_radicado`) FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_encuesta_radicado`.`gera_regional`=TR.`gere_id` WHERE `gera_matriz_id`=? ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY TR.`gere_id_mapa`";
      $consulta_registros_mapa_radicado = $enlace_db->prepare($consulta_string_mapa_radicado);
      $consulta_registros_mapa_radicado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_mapa_radicado->execute();
      $resultado_registros_mapa_radicado = $consulta_registros_mapa_radicado->get_result()->fetch_all(MYSQLI_NUM);

      for ($i=0; $i < count($resultado_registros_mapa_radicado); $i++) { 
          $array_mapa_radicado[$resultado_registros_mapa_radicado[$i][0]]=$resultado_registros_mapa_radicado[$i][1];
      }

      $consulta_string_mapa_efectivo="SELECT TR.`gere_id_mapa`, COUNT(`gera_radicado`) FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_encuesta_radicado`.`gera_regional`=TR.`gere_id` WHERE `gera_matriz_id`=? AND `gera_efectivo`='1' ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY TR.`gere_id_mapa`";
      $consulta_registros_mapa_efectivo = $enlace_db->prepare($consulta_string_mapa_efectivo);
      $consulta_registros_mapa_efectivo->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_mapa_efectivo->execute();
      $resultado_registros_mapa_efectivo = $consulta_registros_mapa_efectivo->get_result()->fetch_all(MYSQLI_NUM);

      for ($i=0; $i < count($resultado_registros_mapa_efectivo); $i++) { 
          $array_mapa_efectivo[$resultado_registros_mapa_efectivo[$i][0]]=$resultado_registros_mapa_efectivo[$i][1];
      }

      for ($i=0; $i < count($resultado_registros_mapa_radicado); $i++) { 
          $array_mapa_efectivo_porcentaje[$resultado_registros_mapa_radicado[$i][0]]=($array_mapa_efectivo[$resultado_registros_mapa_radicado[$i][0]]/$resultado_registros_mapa_radicado[$i][1])*100;
      }

      if ($tipo_mapa=="cantidad") {
        return $array_mapa_efectivo;
      } elseif ($tipo_mapa=="porcentaje") {
        return $array_mapa_efectivo_porcentaje;
      }
    }

    function generar_data_edad($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_opciones = $enlace_db->prepare("SELECT `gera_edad`, COUNT(`gera_radicado`) FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? AND `gera_intentos`>0 ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY `gera_edad`");
      $consulta_opciones->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_opciones->execute();
      $resultado_opciones = $consulta_opciones->get_result()->fetch_all(MYSQLI_NUM);
      
      for ($i=0; $i < count($resultado_opciones); $i++) {
          $array_grupos_graficas_data[$id_variable]['opciones_id'][]=$resultado_opciones[$i][0];
          $array_grupos_graficas_data[$id_variable]['opciones_cantidad'][$resultado_opciones[$i][0]]=$resultado_opciones[$i][1];
          $array_grupos_graficas_data[$id_variable]['opciones_nombres'][$resultado_opciones[$i][0]]=$resultado_opciones[$i][0];
      }

      return $array_grupos_graficas_data;
    }

    function generar_data_genero($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $array_nombre_genero['M']="Masculino";
      $array_nombre_genero['F']="Femenino";
      $array_nombre_genero['NI']="No informa";
      $array_nombre_genero['N']="No informa";
      $array_nombre_genero['']="No diligenciado";


      $consulta_opciones = $enlace_db->prepare("SELECT `gera_genero`, COUNT(`gera_radicado`) FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? AND `gera_intentos`>0 ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY `gera_genero`");
      $consulta_opciones->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_opciones->execute();
      $resultado_opciones = $consulta_opciones->get_result()->fetch_all(MYSQLI_NUM);
      
      for ($i=0; $i < count($resultado_opciones); $i++) {
          $array_grupos_graficas_data[$id_variable]['opciones_id'][]=$resultado_opciones[$i][0];
          $array_grupos_graficas_data[$id_variable]['opciones_cantidad'][$resultado_opciones[$i][0]]=$resultado_opciones[$i][1];
          $array_grupos_graficas_data[$id_variable]['opciones_nombres'][$resultado_opciones[$i][0]]=$array_nombre_genero[$resultado_opciones[$i][0]];
      }

      return $array_grupos_graficas_data;
    }

    function generar_data_matencion($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_opciones = $enlace_db->prepare("SELECT `gera_motivo`, COUNT(`gera_radicado`) FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? AND `gera_intentos`>0 ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY `gera_motivo`");
      $consulta_opciones->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_opciones->execute();
      $resultado_opciones = $consulta_opciones->get_result()->fetch_all(MYSQLI_NUM);
      
      for ($i=0; $i < count($resultado_opciones); $i++) {
          $array_grupos_graficas_data[$id_variable]['opciones_id'][]=$resultado_opciones[$i][0];
          $array_grupos_graficas_data[$id_variable]['opciones_cantidad'][$resultado_opciones[$i][0]]=$resultado_opciones[$i][1];
          $array_grupos_graficas_data[$id_variable]['opciones_nombres'][$resultado_opciones[$i][0]]=$resultado_opciones[$i][0];
      }

      return $array_grupos_graficas_data;
    }

    function generar_data_alertas($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(TR.`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="TR.`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="TR.`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_notificaciones = $enlace_db->prepare("SELECT COUNT(`gern_id`) FROM `tb_gestion_encuesta_registro_notificacion` LEFT JOIN `tb_gestion_encuesta_registro` AS TE ON `tb_gestion_encuesta_registro_notificacion`.`gern_encuesta`=TE.`ger_consecutivo` LEFT JOIN `tb_gestion_encuesta_radicado` AS TR ON TE.`ger_radicado`=TR.`gera_radicado` WHERE TE.`ger_encuesta_id`=? ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta."");
      $consulta_notificaciones->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_notificaciones->execute();
      $resultado_notificaciones = $consulta_notificaciones->get_result()->fetch_all(MYSQLI_NUM);
      
      $consulta_string_gestionado="SELECT COUNT(`gera_contactado`) FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? AND `gera_intentos`>0 ".str_replace('TR.', '', $filtro_anio_consulta)." ".str_replace('TR.', '', $filtro_regional_consulta)." ".str_replace('TR.', '', $filtro_czonal_consulta);
      $consulta_registros_gestionado = $enlace_db->prepare($consulta_string_gestionado);
      $consulta_registros_gestionado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_gestionado->execute();
      $resultado_registros_gestionado = $consulta_registros_gestionado->get_result()->fetch_all(MYSQLI_NUM);

      $array_graficas_data[$id_variable]['opciones_id'][]="alertas";
      $array_graficas_data[$id_variable]['opciones_id'][]="sin_alerta";
      $array_graficas_data[$id_variable]['opciones_nombres']['alertas']="Alertas";
      $array_graficas_data[$id_variable]['opciones_nombres']['sin_alerta']="Sin alertas";
      $array_graficas_data[$id_variable]['opciones_cantidad']['alertas']=$resultado_notificaciones[0][0];
      $array_graficas_data[$id_variable]['opciones_cantidad']['sin_alerta']=$resultado_registros_gestionado[0][0]-$resultado_notificaciones[0][0];

      return $array_graficas_data;
    }

    function generar_data_indicador_gral($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_string_contactado="SELECT SUM(`gera_contactado`) FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta;
      $consulta_registros_contactado = $enlace_db->prepare($consulta_string_contactado);
      $consulta_registros_contactado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_contactado->execute();
      $resultado_registros_contactado = $consulta_registros_contactado->get_result()->fetch_all(MYSQLI_NUM);

      $consulta_string_efectivo="SELECT SUM(`gera_efectivo`) FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta;
      $consulta_registros_efectivo = $enlace_db->prepare($consulta_string_efectivo);
      $consulta_registros_efectivo->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_efectivo->execute();
      $resultado_registros_efectivo = $consulta_registros_efectivo->get_result()->fetch_all(MYSQLI_NUM);

      $consulta_string_intentos="SELECT SUM(`gera_intentos`) FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta;
      $consulta_registros_intentos = $enlace_db->prepare($consulta_string_intentos);
      $consulta_registros_intentos->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_intentos->execute();
      $resultado_registros_intentos = $consulta_registros_intentos->get_result()->fetch_all(MYSQLI_NUM);

      $consulta_string_gestionado="SELECT COUNT(`gera_contactado`) FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? AND `gera_intentos`>0 ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta;
      $consulta_registros_gestionado = $enlace_db->prepare($consulta_string_gestionado);
      $consulta_registros_gestionado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_gestionado->execute();
      $resultado_registros_gestionado = $consulta_registros_gestionado->get_result()->fetch_all(MYSQLI_NUM);

      $indicador_gral['contactabilidad']=($resultado_registros_contactado[0][0]/$resultado_registros_gestionado[0][0])*100;
      $indicador_gral['efectividad']=($resultado_registros_efectivo[0][0]/$resultado_registros_contactado[0][0])*100;
      $indicador_gral['marcacion']=($resultado_registros_intentos[0][0]/$resultado_registros_gestionado[0][0])*100;

      return $indicador_gral;
    }

    function generar_data_indicador_agente($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_string_gral_abierto_cerrado="SELECT `gera_usuario_gestion`, `gera_estado_gestion`, COUNT(`gera_radicado`), TU.`usu_nombres_apellidos` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` WHERE `gera_matriz_id`=? ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY `gera_usuario_gestion`, `gera_estado_gestion` ORDER BY TU.`usu_nombres_apellidos` ASC";
      $consulta_registros_gral_abierto_cerrado = $enlace_db->prepare($consulta_string_gral_abierto_cerrado);
      $consulta_registros_gral_abierto_cerrado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_gral_abierto_cerrado->execute();
      $resultado_registros_gral_abierto_cerrado = $consulta_registros_gral_abierto_cerrado->get_result()->fetch_all(MYSQLI_NUM);

      for ($i=0; $i < count($resultado_registros_gral_abierto_cerrado); $i++) { 
          $array_gral_abce_agentes[]=$resultado_registros_gral_abierto_cerrado[$i][0];
          $array_gral_abce_agentes_nombre[$resultado_registros_gral_abierto_cerrado[$i][0]]=$resultado_registros_gral_abierto_cerrado[$i][3];
      }

      $array_gral_abce_agentes=array_values(array_unique($array_gral_abce_agentes));

      for ($i=0; $i < count($array_gral_abce_agentes); $i++) { 
          $array_gral_abierto_cerrado[$array_gral_abce_agentes[$i]]['Pendiente']+=0;
          $array_gral_abierto_cerrado[$array_gral_abce_agentes[$i]]['Cerrado']+=0;
          $array_gral_abierto_cerrado[$array_gral_abce_agentes[$i]]['Efectivo']+=0;
          
      }
      
      for ($i=0; $i < count($resultado_registros_gral_abierto_cerrado); $i++) { 
          $array_gral_abierto_cerrado[$resultado_registros_gral_abierto_cerrado[$i][0]][$resultado_registros_gral_abierto_cerrado[$i][1]]+=$resultado_registros_gral_abierto_cerrado[$i][2];
      }

      $array_gral_data['agentes']=$array_gral_abce_agentes;
      $array_gral_data['agentes_nombres']=$array_gral_abce_agentes_nombre;
      $array_gral_data['data']=$array_gral_abierto_cerrado;

      return $array_gral_data;
    }

    function generar_data_indicador_geagente($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_string_gral_abierto_cerrado="SELECT `gera_usuario_gestion`, COUNT(`gera_radicado`), TU.`usu_nombres_apellidos` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` WHERE `gera_matriz_id`=? AND `gera_intentos`>0 ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY `gera_usuario_gestion` ORDER BY TU.`usu_nombres_apellidos` ASC";
      $consulta_registros_gral_abierto_cerrado = $enlace_db->prepare($consulta_string_gral_abierto_cerrado);
      $consulta_registros_gral_abierto_cerrado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_gral_abierto_cerrado->execute();
      $resultado_registros_gral_abierto_cerrado = $consulta_registros_gral_abierto_cerrado->get_result()->fetch_all(MYSQLI_NUM);

      for ($i=0; $i < count($resultado_registros_gral_abierto_cerrado); $i++) { 
          $array_gral_abce_agentes[]=$resultado_registros_gral_abierto_cerrado[$i][0];
          $array_gral_abce_agentes_nombre[$resultado_registros_gral_abierto_cerrado[$i][0]]=$resultado_registros_gral_abierto_cerrado[$i][2];
      }

      $array_gral_abce_agentes=array_values(array_unique($array_gral_abce_agentes));

      for ($i=0; $i < count($array_gral_abce_agentes); $i++) { 
          $array_gral_abierto_cerrado[$array_gral_abce_agentes[$i]]['Gestionadas']+=0;
          
      }
      
      for ($i=0; $i < count($resultado_registros_gral_abierto_cerrado); $i++) { 
          $array_gral_abierto_cerrado[$resultado_registros_gral_abierto_cerrado[$i][0]]['Gestionadas']+=$resultado_registros_gral_abierto_cerrado[$i][1];
      }

      $array_gral_data['agentes']=$array_gral_abce_agentes;
      $array_gral_data['agentes_nombres']=$array_gral_abce_agentes_nombre;
      $array_gral_data['data']=$array_gral_abierto_cerrado;

      return $array_gral_data;
    }

    function generar_data_indicador_coagente($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_string_gral_abierto_cerrado="SELECT `gera_usuario_gestion`, COUNT(`gera_radicado`), TU.`usu_nombres_apellidos` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` WHERE `gera_matriz_id`=? AND `gera_contactado`='1' ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY `gera_usuario_gestion` ORDER BY TU.`usu_nombres_apellidos` ASC";
      $consulta_registros_gral_abierto_cerrado = $enlace_db->prepare($consulta_string_gral_abierto_cerrado);
      $consulta_registros_gral_abierto_cerrado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_gral_abierto_cerrado->execute();
      $resultado_registros_gral_abierto_cerrado = $consulta_registros_gral_abierto_cerrado->get_result()->fetch_all(MYSQLI_NUM);

      for ($i=0; $i < count($resultado_registros_gral_abierto_cerrado); $i++) { 
          $array_gral_abce_agentes[]=$resultado_registros_gral_abierto_cerrado[$i][0];
          $array_gral_abce_agentes_nombre[$resultado_registros_gral_abierto_cerrado[$i][0]]=$resultado_registros_gral_abierto_cerrado[$i][2];
      }

      $array_gral_abce_agentes=array_values(array_unique($array_gral_abce_agentes));

      for ($i=0; $i < count($array_gral_abce_agentes); $i++) { 
          $array_gral_abierto_cerrado[$array_gral_abce_agentes[$i]]['Contactadas']+=0;
          
      }
      
      for ($i=0; $i < count($resultado_registros_gral_abierto_cerrado); $i++) { 
          $array_gral_abierto_cerrado[$resultado_registros_gral_abierto_cerrado[$i][0]]['Contactadas']+=$resultado_registros_gral_abierto_cerrado[$i][1];
      }

      $array_gral_data['agentes']=$array_gral_abce_agentes;
      $array_gral_data['agentes_nombres']=$array_gral_abce_agentes_nombre;
      $array_gral_data['data']=$array_gral_abierto_cerrado;

      return $array_gral_data;
    }

    function generar_data_indicador_efagente($enlace_db, $id_encuesta = NULL, $id_variable = NULL, $array_filtros = NULL) {
      $data_consulta=array();
      array_push($data_consulta, $id_encuesta);

      $filtro_anio_consulta="";
      if (count($array_filtros['filtro_anio_mes'])>0) {
          $filtro_anio_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_anio_mes']); $i++) { 
              $filtro_anio_consulta.="DATE_FORMAT(`gera_registro_fecha`, '%Y-%m')=? OR ";
              array_push($data_consulta, $array_filtros['filtro_anio_mes'][$i]);
          }
          $filtro_anio_consulta=substr($filtro_anio_consulta, 0, -4).")";
      }

      $filtro_regional_consulta="";
      if (count($array_filtros['filtro_regional'])>0) {
          $filtro_regional_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_regional']); $i++) { 
              $filtro_regional_consulta.="`gera_regional`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_regional'][$i]);
          }
          $filtro_regional_consulta=substr($filtro_regional_consulta, 0, -4).")";
      }

      $filtro_czonal_consulta="";
      if (count($array_filtros['filtro_centro_zonal'])>0) {
          $filtro_czonal_consulta="AND (";
          for ($i=0; $i < count($array_filtros['filtro_centro_zonal']); $i++) { 
              $filtro_czonal_consulta.="`gera_centro_zonal`=? OR ";
              array_push($data_consulta, $array_filtros['filtro_centro_zonal'][$i]);
          }
          $filtro_czonal_consulta=substr($filtro_czonal_consulta, 0, -4).")";
      }

      $consulta_string_gral_abierto_cerrado="SELECT `gera_usuario_gestion`, COUNT(`gera_radicado`), TU.`usu_nombres_apellidos` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` WHERE `gera_matriz_id`=? AND `gera_efectivo`='1' ".$filtro_anio_consulta." ".$filtro_regional_consulta." ".$filtro_czonal_consulta." GROUP BY `gera_usuario_gestion` ORDER BY TU.`usu_nombres_apellidos` ASC";
      $consulta_registros_gral_abierto_cerrado = $enlace_db->prepare($consulta_string_gral_abierto_cerrado);
      $consulta_registros_gral_abierto_cerrado->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
      $consulta_registros_gral_abierto_cerrado->execute();
      $resultado_registros_gral_abierto_cerrado = $consulta_registros_gral_abierto_cerrado->get_result()->fetch_all(MYSQLI_NUM);

      for ($i=0; $i < count($resultado_registros_gral_abierto_cerrado); $i++) { 
          $array_gral_abce_agentes[]=$resultado_registros_gral_abierto_cerrado[$i][0];
          $array_gral_abce_agentes_nombre[$resultado_registros_gral_abierto_cerrado[$i][0]]=$resultado_registros_gral_abierto_cerrado[$i][2];
      }

      $array_gral_abce_agentes=array_values(array_unique($array_gral_abce_agentes));

      for ($i=0; $i < count($array_gral_abce_agentes); $i++) { 
          $array_gral_abierto_cerrado[$array_gral_abce_agentes[$i]]['Efectivas']+=0;
          
      }
      
      for ($i=0; $i < count($resultado_registros_gral_abierto_cerrado); $i++) { 
          $array_gral_abierto_cerrado[$resultado_registros_gral_abierto_cerrado[$i][0]]['Efectivas']+=$resultado_registros_gral_abierto_cerrado[$i][1];
      }

      $array_gral_data['agentes']=$array_gral_abce_agentes;
      $array_gral_data['agentes_nombres']=$array_gral_abce_agentes_nombre;
      $array_gral_data['data']=$array_gral_abierto_cerrado;

      return $array_gral_data;
    }
?>