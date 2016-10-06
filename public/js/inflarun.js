$(document).ready(function() {
	// Opciones del DatePicker
	$(".datetimepicker").datepicker({
		format: "yyyy/mm/dd",
		todayHighlight: true,
		autoclose: true,
		startDate: "1900/01/01",
		endDate: new Date()
	});
	
	// Añade clase 'Active' a los enlaces de la barra de navegación.
	$("#navbarCollapse .nav a").click(function() {
		$("#navbarCollapse ul li").each(function() {
			$(this).removeClass("active");
		});
		$(this).parent().addClass("active");
	});

	// Formulario de registro.
	$("#btnSuscribirse").click(function(e) {
		e.preventDefault();
		$(".alert").remove();
		$("#btnSuscribirse").prop("disabled", true);
		$("#formRegistro").prepend("\
			<div class='progress'>\n\
				<div class='progress-bar progress-bar-striped active' aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'>\n\
					Estamos registrándote en el sistema. ¡Solo un momento!\n\
				</div>\n\
			</div>"
		);
		var datos = {
			nombre: $("#nombreInscripcion").val(),
			paterno: $("#paternoInscripcion").val(),
			materno: $("#maternoInscripcion").val(),
			sexo: $("#sexoInscripcion").val(),
			fechaNacimiento: $("#fechaNacimiento").val(),
			email: $("#emailInscripcion").val(),
			pwd: $("#pwdInscripcion").val(),
			pwdRepetida: $("#pwdRepetidaInscripcion").val(),
			estado: $("#estadoInscripcion").val(),
			boletin: $("#chkBoletin").is(":checked") ? 1 : 0
		};

		$.post("/InflaRun/public/application/suscribirse", datos, function(respuesta) {
			var alert;

			if (respuesta.code === 0) {
				alert = "<div class='alert alert-success fade in'>";
			} else {
				$("#btnSuscribirse").prop("disabled", false);
				alert = "<div class='alert alert-danger fade in'>";
			}
			
			$(".progress").remove();
			$("#formRegistro").prepend(alert + respuesta.message + "</div>");
		});
	});

	// Limpia el formulario de registro.
	$("#formularioRegistro").on("hidden.bs.modal", function() {
		$(".alert").remove();
		$("#btnSuscribirse").prop("disabled", false);
	});
});