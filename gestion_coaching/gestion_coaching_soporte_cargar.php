<?php
    $modulo_plataforma = 'Coaching';
    require_once('../config/validaciones_seguridad.php');
    require_once('../config/conexion_db.php');
    require_once('lib/coaching_seguridad.php');
    require_once('lib/coaching_complementos.php');

    $perfil = coachingPerfilUsuarioActual();
    $gcp_id = validar_input(base64_decode($_GET['reg'] ?? ''));

    if (!$perfil || !usuarioPuedeVerPaquete($enlace_db, $_SESSION['usu_id'], $perfil, $gcp_id)) {
        header('Location:../permiso_denegado.php');
        exit;
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    $mensaje = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['_csrf_token']) || !hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token'])) {
            $mensaje = "<div class='coaching_aviso_error'><span class='fas fa-exclamation-circle'></span> Solicitud inválida (CSRF). Recargue e intente de nuevo.</div>";
        } else {
            try {
                guardarSoporteCoaching($enlace_db, $gcp_id, $_FILES['soporte'] ?? [], trim($_POST['tipo_documental'] ?? 'Evidencia'), $_SESSION['usu_id']);
                header('Location:gestion_coaching_ver.php?reg=' . base64_encode($gcp_id));
                exit;
            } catch (Throwable $e) {
                $mensaje = "<div class='coaching_aviso_error'><span class='fas fa-exclamation-circle'></span> " . coachingEsc($e->getMessage()) . "</div>";
            }
        }
    }

    $titulo_header = 'Coaching | Adjuntar soporte';
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include('../config/configuracion_estilos.php'); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }
        .coaching_aviso_error { background: #FDEDED; border: 1px solid #FF0000; color: #FF0000; border-radius: 5px; padding: 10px 12px; font-size: 12px; margin-bottom: 14px; }
        label.coaching_label { font-weight: bold; font-size: 12px; margin-bottom: 6px; display: block; color: #1A1A1A; }
        label.coaching_label .opcional { font-weight: normal; color: #6E6E6E; font-size: 10px; }

        .coaching_dropzone {
            border: 2px dashed #F2F2F2; border-radius: 8px; padding: 30px 20px; text-align: center;
            cursor: pointer; transition: border-color .15s, background .15s; background: #FFFFFF;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .coaching_dropzone:hover, .coaching_dropzone.arrastrando { border-color: #4CAF50; background: #F1F8F2; }
        .coaching_dropzone .icono { font-size: 34px; color: #B0B4B8; margin-bottom: 10px; }
        .coaching_dropzone.arrastrando .icono, .coaching_dropzone:hover .icono { color: #4CAF50; }
        .coaching_dropzone .texto_principal { font-size: 13px; color: #1A1A1A; font-weight: bold; }
        .coaching_dropzone .texto_secundario { font-size: 11px; color: #6E6E6E; margin-top: 4px; }
        .coaching_dropzone input[type="file"] { display: none; }

        .coaching_archivo_elegido {
            display: none; align-items: center; gap: 10px; background: #F1F8F2; border: 1px solid #4CAF50;
            border-radius: 6px; padding: 10px 12px; margin-top: 12px;
        }
        .coaching_archivo_elegido.visible { display: flex; }
        .coaching_archivo_elegido .icono { font-size: 20px; color: #4CAF50; }
        .coaching_archivo_elegido .info { flex: 1; min-width: 0; }
        .coaching_archivo_elegido .nombre { font-size: 12px; font-weight: bold; color: #1A1A1A; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .coaching_archivo_elegido .tamano { font-size: 10px; color: #6E6E6E; }
        .coaching_archivo_elegido .quitar { background: none; border: none; color: #6E6E6E; cursor: pointer; font-size: 14px; }
        .coaching_archivo_elegido .quitar:hover { color: #FF0000; }

        #btn_cargar[disabled] { opacity: .7; cursor: not-allowed; }
    </style>
</head>
<body>
    <?php include('../menu_principal.php'); include('../menu_header.php'); ?>
    <div class="contenido">
        <nav class="coaching_breadcrumb">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes">Coaching</a>
            <span class="mx-1">/</span>
            <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>"><?php echo htmlspecialchars($gcp_id); ?></a>
            <span class="mx-1">/</span>
            <span>Adjuntar soporte</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-3">
                    <h4 class="titulo_seccion mb-0">Adjuntar soporte</h4>
                    <span class="descripcion-seccion-conocimiento">Paquete <?php echo htmlspecialchars($gcp_id); ?></span>
                </div>

                <div class="cuadro_dash">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-paperclip"></span> Adjuntar soporte</div>
                    <div class="p-3">
                        <?php echo $mensaje; ?>
                        <form method="post" enctype="multipart/form-data" id="form_soporte">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">

                            <label class="coaching_label" for="tipo_documental">Tipo documental</label>
                            <select class="form-control mb-3" name="tipo_documental" id="tipo_documental">
                                <option>Evidencia</option>
                                <option>Taller</option>
                                <option>Compromiso</option>
                                <option>Seguimiento</option>
                                <option>Reconocimiento</option>
                            </select>

                            <label class="coaching_label">Archivo <span class="opcional">(máximo 10 MB — pdf, doc, docx, xls, xlsx, jpg, png)</span></label>

                            <label class="coaching_dropzone" id="dropzone" for="soporte">
                                <div class="icono"><span class="fas fa-cloud-upload-alt"></span></div>
                                <div class="texto_principal">Arrastre su archivo aquí, o haga clic para seleccionar</div>
                                <div class="texto_secundario">PDF, Word, Excel o imagen — máximo 10 MB</div>
                                <input type="file" name="soporte" id="soporte" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                            </label>

                            <div class="coaching_archivo_elegido" id="archivo_elegido">
                                <span class="icono fas fa-file" id="archivo_icono"></span>
                                <div class="info">
                                    <div class="nombre" id="archivo_nombre"></div>
                                    <div class="tamano" id="archivo_tamano"></div>
                                </div>
                                <button type="button" class="quitar" id="btn_quitar_archivo" title="Quitar">
                                    <span class="fas fa-times-circle"></span>
                                </button>
                            </div>

                            <div class="mt-4" style="display:flex; justify-content:center; align-items:center; gap:10px;">
                                <button type="submit" id="btn_cargar" class="btn-corp px-4 py-2" style="border-radius:5px; border:0;">
                                    <span class="fas fa-upload"></span> Cargar
                                </button>
                                <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp-2 px-4 py-2 d-inline-block" style="border-radius:5px;">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var dropzone = document.getElementById('dropzone');
            var inputArchivo = document.getElementById('soporte');
            var cajaElegido = document.getElementById('archivo_elegido');
            var nombreEl = document.getElementById('archivo_nombre');
            var tamanoEl = document.getElementById('archivo_tamano');
            var iconoEl = document.getElementById('archivo_icono');
            var btnQuitar = document.getElementById('btn_quitar_archivo');
            var textoPrincipal = dropzone.querySelector('.texto_principal');
            var textoSecundario = dropzone.querySelector('.texto_secundario');

            var ICONOS = {
                pdf: 'fa-file-pdf', doc: 'fa-file-word', docx: 'fa-file-word',
                xls: 'fa-file-excel', xlsx: 'fa-file-excel',
                jpg: 'fa-file-image', jpeg: 'fa-file-image', png: 'fa-file-image'
            };

            function formatearTamano(bytes) {
                if (bytes < 1024) { return bytes + ' B'; }
                if (bytes < 1024 * 1024) { return (bytes / 1024).toFixed(0) + ' KB'; }
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            }

            function mostrarArchivo(archivo) {
                var ext = archivo.name.split('.').pop().toLowerCase();
                iconoEl.className = 'icono fas ' + (ICONOS[ext] || 'fa-file');
                nombreEl.textContent = archivo.name;
                tamanoEl.textContent = formatearTamano(archivo.size);
                cajaElegido.classList.add('visible');
                textoPrincipal.textContent = 'Archivo listo — haga clic para cambiarlo';
                textoSecundario.textContent = archivo.name;
            }

            inputArchivo.addEventListener('change', function () {
                if (inputArchivo.files.length > 0) { mostrarArchivo(inputArchivo.files[0]); }
            });

            btnQuitar.addEventListener('click', function (e) {
                e.preventDefault();
                inputArchivo.value = '';
                cajaElegido.classList.remove('visible');
                textoPrincipal.textContent = 'Arrastre su archivo aquí, o haga clic para seleccionar';
                textoSecundario.textContent = 'PDF, Word, Excel o imagen — máximo 10 MB';
            });

            ['dragenter', 'dragover'].forEach(function (evento) {
                dropzone.addEventListener(evento, function (e) {
                    e.preventDefault(); e.stopPropagation();
                    dropzone.classList.add('arrastrando');
                });
            });
            ['dragleave', 'drop'].forEach(function (evento) {
                dropzone.addEventListener(evento, function (e) {
                    e.preventDefault(); e.stopPropagation();
                    dropzone.classList.remove('arrastrando');
                });
            });
            dropzone.addEventListener('drop', function (e) {
                var archivos = e.dataTransfer.files;
                if (archivos.length > 0) {
                    inputArchivo.files = archivos;
                    mostrarArchivo(archivos[0]);
                }
            });

            var form = document.getElementById('form_soporte');
            var boton = document.getElementById('btn_cargar');
            form.addEventListener('submit', function () {
                setTimeout(function () {
                    boton.disabled = true;
                    boton.innerHTML = '<span class="fas fa-spinner fa-spin"></span> Cargando...';
                }, 0);
            });
        })();
        </script>
    </div>
    <?php include('../footer.php'); ?>
</body>
</html>
