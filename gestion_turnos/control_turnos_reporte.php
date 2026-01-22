<div class="modal fade" id="dataexport" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Reportes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form name="reportes_exportar" action="control_turnos_reporte_excel.php" method="POST">
        <div class="modal-body">
            <div class="contenido_pagina_modal">
                <div class="row">
                  <div class="col-md-12">
                      <div class="form-group">
                          <label for="tipo_reporte">Tipo reporte</label>
                          <select class="form-control form-control-sm" name="tipo_reporte" id="tipo_reporte" required>
                              <option value="">Seleccione</option>
                              <option value="Consolidado">Consolidado</option>
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