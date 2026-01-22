<div class="modal fade" id="datafilter_regional" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Filtro Regional</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form name="aplicar_filtro" action="" method="POST">
        <div class="modal-body">
            <div class="contenido_pagina_modal">
                <div class="row">
                  <div class="col-md-12">
                      <div class="form-group">
                          <label for="filtro_regional">Regional</label>
                          <select class="form-control form-control-sm" name="filtro_regional" id="filtro_regional" required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_regional); $i++): ?>
                                <option value="<?php echo $resultado_regional[$i][0]; ?>"><?php echo $resultado_regional[$i][1]; ?></option>
                              <?php endfor; ?>
                          </select>
                      </div>
                  </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-corp-form" name="aplicar_filtro">Aceptar</button>
        </div>
      </form>
    </div>
  </div>
</div>