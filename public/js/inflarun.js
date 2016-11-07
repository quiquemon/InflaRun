$(document).ready(function() {
	// Opciones del DatePicker
	$(".datetimepicker").datepicker({
		format: "yyyy-mm-dd",
		todayHighlight: true,
		autoclose: true,
		startDate: "1900-01-01",
		endDate: new Date()
	});
	
	// Añade clase 'Active' a los enlaces de la barra de navegación.
	$("#navbarCollapse .nav a").click(function() {
		$("#navbarCollapse ul li").each(function() {
			$(this).removeClass("active");
		});
		$(this).parent().addClass("active");
	});
});
