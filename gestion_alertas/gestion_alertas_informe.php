<div class="modal fade" id="datainforme" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Informes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-body">
            <div class="contenido_pagina_modal">
                <div class="row">
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
                  <div class="col-md-12" id="informe_lista"></div>
                  <div class="col-md-12 d-none" id="div_observaciones">
                      <div class="form-group">
                        <label for="observaciones">Observaciones/comentarios</label>
                        <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" rows="8"></textarea>
                      </div>
                  </div>
                  <div class="col-md-12" id="informe_estado"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning d-none" name="enviar_informe" id="enviar_informe">Enviar informe</button>
            <button type="submit" class="btn btn-corp-form" name="crear_informe" id="crear_informe">Generar informe</button>
        </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function(){
      $("#crear_informe").click(function(){
          var fecha_inicio = document.getElementById("fecha_inicio").value;
          var fecha_fin = document.getElementById("fecha_fin").value;


          $.ajax({
              url: 'gestion_alertas_informe_generar.php',
              type: 'POST',
              data: {inicio:fecha_inicio, fin:fecha_fin, acc:'generar'},
              dataType: 'json',
              success:function(data){
                  var informe_lista = data['informe_lista'];
                  var informe_estado = data['informe_estado'];

                  $("#informe_lista").html(informe_lista);
                  $("#informe_estado").html('');
                  if (informe_estado=="generado") {
                    $("#div_observaciones").removeClass('d-none').addClass('d-block');
                    $("#enviar_informe").removeClass('d-none').addClass('d-block');

                  } else {
                    $("#div_observaciones").removeClass('d-block').addClass('d-none');
                    $("#enviar_informe").removeClass('d-block').addClass('d-none');
                  }
              }
          });
      });

      $("#enviar_informe").click(function(){
          var fecha_inicio = document.getElementById("fecha_inicio").value;
          var fecha_fin = document.getElementById("fecha_fin").value;
          var observaciones = document.getElementById("observaciones").value;

          $.ajax({
              url: 'gestion_alertas_informe_generar.php',
              type: 'POST',
              data: {inicio:fecha_inicio, fin:fecha_fin, observaciones:observaciones, acc:'enviar'},
              dataType: 'json',
              success:function(data){
                  var informe_lista = data['informe_lista'];
                  var informe_estado = data['informe_estado'];
                  
                  $("#informe_lista").html(informe_lista);

                  if (informe_estado=="enviado") {
                    $("#informe_estado").html('<p class="alert alert-success col-md-12 p-1 font-size-11 mb-1"><span class="fas fa-exclamation-triangle"></span> ¡Informe enviado exitosamente!</p>');
                    $("#div_observaciones").removeClass('d-block').addClass('d-none');
                    $("#enviar_informe").removeClass('d-block').addClass('d-none');
                    document.getElementById("observaciones").value="";

                  } else {
                    if (informe_estado=="no_enviado") {
                      $("#informe_estado").html('<p class="alert alert-danger col-md-12 p-1 font-size-11 mb-1"><span class="fas fa-exclamation-triangle"></span> ¡Problemas al enviar el informe, por favor verifique e intente nuevamente!</p>');
                      $("#div_observaciones").removeClass('d-none').addClass('d-block');
                      $("#enviar_informe").removeClass('d-none').addClass('d-block');
                    } else {
                      $("#informe_estado").html(informe_estado);
                      $("#div_observaciones").removeClass('d-block').addClass('d-none');
                      $("#enviar_informe").removeClass('d-block').addClass('d-none');
                    }

                  }

              }
          });
      });

  });
</script>