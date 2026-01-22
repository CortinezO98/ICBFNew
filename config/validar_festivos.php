<?php
	/**
	 * Clase para calcular festivos de Colombia.
	 * @since 2018-05-01
	 * @version 1.0
	 * @lastModify 2019-04-30
	 */
	class festivos {

		private $hoy;
		private $festivos;
		private $anio;
		private $pascua_mes;
		private $pascua_dia;
		
		/**
		 * Constructor de la clase Festivos
		 * @param  {String-Integer} $anio [Recibe como parámetro el año a validar]
		 * @return {null}                 [Sin retorno]
		 */
		public function festivos($anio='') {
			$this->hoy=date('d/m/Y');
			
			if($anio=='')
				$anio = date('Y');
				
			$this->anio = $anio;
			
			$this->pascua_mes = date("m", easter_date($this->anio));
			$this->pascua_dia = date("d", easter_date($this->anio));
					
			$this->festivos[$anio][1][1]   = true;		// Primero de Enero
			$this->festivos[$anio][5][1]   = true;		// Dia del Trabajo 1 de Mayo
			$this->festivos[$anio][7][20]  = true;		// Independencia 20 de Julio
			$this->festivos[$anio][8][7]   = true;		// Batalla de Boyacá 7 de Agosto
			$this->festivos[$anio][12][8]  = true;		// Maria Inmaculada 8 diciembre (religiosa)
			$this->festivos[$anio][12][25] = true;		// Navidad 25 de diciembre
			
			$this->calculateEmiliani(1, 6);				// Reyes Magos Enero 6
			$this->calculateEmiliani(3, 19);				// San Jose Marzo 19
			$this->calculateEmiliani(6, 29);				// San Pedro y San Pablo Junio 29
			$this->calculateEmiliani(8, 15);				// Asunción Agosto 15
			$this->calculateEmiliani(10, 12);			// Descubrimiento de América Oct 12
			$this->calculateEmiliani(11, 1);				// Todos los santos Nov 1
			$this->calculateEmiliani(11, 11);			// Independencia de Cartagena Nov 11
			
			//otras fechas calculadas a partir de la pascua.
			
			$this->otherDatesCalculated(-3);			//jueves santo
			$this->otherDatesCalculated(-2);			//viernes santo
			
			$this->otherDatesCalculated(43,true);		//Ascención el Señor pascua
			$this->otherDatesCalculated(60,true);		//Corpus Cristi
			$this->otherDatesCalculated(68,true);		//Sagrado Corazón
			
			// otras fechas importantes que no son festivos

			// $this->otherDatesCalculated(-46);		// Miércoles de Ceniza
			// $this->otherDatesCalculated(-46);		// Miércoles de Ceniza
			// $this->otherDatesCalculated(-48);		// Lunes de Carnaval Barranquilla
			// $this->otherDatesCalculated(-47);		// Martes de Carnaval Barranquilla
		}

		/**
	     * Función que mueve una fecha diferente a lunes al siguiente lunes en el calendario y se aplica a fechas que estan bajo la ley emiliani global
	     * @param  {Integer} $mes_festivo [Mes en el que se va calcular]
	     * @param  {Integer} $dia_festivo [Día en el que se va calcular]
	     * @return {null}             	  [Sin retorno]
	     */
		protected function calculateEmiliani($mes_festivo, $dia_festivo) {
			$dd = date('w', mktime(0, 0, 0, $mes_festivo, $dia_festivo, $this->anio));//mktime or strtotime
			switch ($dd) {
				case 0:                                    // Domingo
					$dia_festivo = $dia_festivo + 1;
					break;
				case 2:                                    // Martes.
					$dia_festivo = $dia_festivo + 6;
					break;
				case 3:                                    // Miércoles
					$dia_festivo = $dia_festivo + 5;
					break;
				case 4:                                     // Jueves
					$dia_festivo = $dia_festivo + 4;
					break;
				case 5:                                     // Viernes
					$dia_festivo = $dia_festivo + 3;
					break;
				case 6:                                     // Sábado
					$dia_festivo = $dia_festivo + 2;
					break;
			}
			$mes = date("n", mktime(0, 0, 0, $mes_festivo, $dia_festivo, $this->anio))+0;//mktime or strtotime
			$dia = date("d", mktime(0, 0, 0, $mes_festivo, $dia_festivo, $this->anio))+0;//mktime or strtotime
			$this->festivos[$this->anio][$mes][$dia] = true;
		}
		
		/**
		 * Función que calcula otras fechas a partir de la pascua o fechas no festivas pero son importantes
		 * @param  {Number}  $cantidadDias   [Cantidad de días a sumar o restar]
		 * @param  {Boolean} $siguienteLunes [Si se debe calcular a partir de la pascua]
		 * @return {null}                    [Sin retorno]
		 */
		protected function otherDatesCalculated($cantidadDias=0, $siguienteLunes=false) {
			$mes_festivo = date("n", mktime(0, 0, 0, $this->pascua_mes, $this->pascua_dia+$cantidadDias, $this->anio));//mktime or strtotime
			$dia_festivo = date("d", mktime(0, 0, 0, $this->pascua_mes, $this->pascua_dia+$cantidadDias, $this->anio));//mktime or strtotime
			
			if ($siguienteLunes) {
				$this->calculateEmiliani($mes_festivo, $dia_festivo);
			} else {	
				$this->festivos[$this->anio][$mes_festivo+0][$dia_festivo+0] = true;
			}
		}

		/**
		 * Función para verificar si la fecha obtenida por parámetro es festiva (el año se pasa como parámetro en el constructor)
		 * @param  {Integer} $dia [Día a validar]
		 * @param  {Integer} $mes [Mes a validar]
		 * @return {Boolean}      [Retorna un booleano con la respuesta]
		 */
		public function esFestivo($dia, $mes) {
			if ($dia=='' || $mes=='') {
				return false;
			}
			
			if (isset($this->festivos[$this->anio][(int)$mes][(int)$dia])) {
				return true;
			} else {
				return false;
			}
		}	
	}
?>