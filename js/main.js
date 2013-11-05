	$(document).ready(function() {
		function checkModules($module) {
			if ($module.is(":checked")) {
				$module.siblings("ul").children("li:first-child").children(".jqPrivileges").attr("checked","checked");
				$module.siblings("ul").children("li").children(".jqPrivileges").removeAttr("disabled");
			} else {
				$module.siblings("ul").children("li").children(".jqPrivileges").attr("disabled","disabled");
			}
		}

		//at initial load/error load, check to see if we need to do set any module privileges up
		$(".jqModule").each(function() {
			checkModules($(this));
		});

		$(".jqModule").change(function() {
			checkModules($(this));
		});

		$("#makeRequest").submit(function() {
			if ($("#jqMath").val() == "five") {
				var error = false;
				$("input[type=\"text\"]").each(function() {
					if (!$(this).val()) {
						error = $(this).attr("name");
						return false;
					}
				});
				if (!error) {
					if ($("#password").val() && ($("#password").val() == $("#confirmPassword").val())) {
						var modCount = 0;
						$(".jqModule").each(function() {
							if ($(this).is(":checked")) {
								modCount++;
							}
						});
						if (modCount > 0) {
							return true;
						} else {
							alert("You must select at least one module.");
							return false;
						}
					}
					alert("You must enter and confirm a password.");
					return false;
				}
				if (error) {
					var field = ($("label[for=\""+error+"\"]").text()) ? $("label[for=\""+error+"\"]").text():error;
					alert(field+" is required.");
				}
				return false;
			}
			alert("Please check (and spell) your math.");
			return false;
		});
	});
