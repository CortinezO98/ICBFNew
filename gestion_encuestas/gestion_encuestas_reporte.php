<div class="modal fade" id="dataexport" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Reportes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form name="reportes_exportar" action="gestion_encuestas_reporte_excel.php" method="POST">
        <div class="modal-body">
            <div class="contenido_pagina_modal">
                <div class="row">
                  <div class="col-md-12">
                      <div class="form-group">
                          <label for="id_campania">Campaña</label>
                          <select class="form-control form-control-sm" name="id_campania" id="id_campania" required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_registros_matriz); $i++): ?>
                                <option value="<?php echo $resultado_registros_matriz[$i][0]; ?>"><?php echo $resultado_registros_matriz[$i][1]; ?></option>
                              <?php endfor; ?>
                          </select>
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="form-group">
                          <label for="estado">Estado</label>
                          <select class="form-control form-control-sm" name="estado" id="estado" required>
                              <option value="">Seleccione</option>
                              <option value="Pendiente">Pendiente</option>
                              <option value="Cerrado">Cerrado</option>
                              <option value="Cancelado">Cancelado</option>
                              <option value="Todos">Todos</option>
                          </select>
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="form-group">
                          <label for="tipo_reporte">Tipo reporte</label>
                          <select class="form-control form-control-sm" name="tipo_reporte" id="tipo_reporte" required>
                              <option value="">Seleccione</option>
                              <option value="Radicados">Radicados</option>
                              <option value="Encuestas">Encuestas</option>
                              <option value="Control Cambios">Control Cambios</option>
                              <option value="Alertas">Alertas</option>
                          </select>
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="form-group">
                        <label for="fecha_inicio">Fecha inicio</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_inicio" id="fecha_inicio" value="" required>
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="form-group">
                        <label for="fecha_fin">Fecha fin</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_fin" id="fecha_fin" value="" required>
                      </div>
                  </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-corp-form" name="exportar_reporte">Generar reporte</button>
        </div>
      </form>
    </div>
  </div>
</div>