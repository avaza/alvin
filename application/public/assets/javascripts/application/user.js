jQNC(document).ready(function(){
	jQNC("#submit").click(function(){
		//DATA VARIABLES
		var fname = $("#fname").val();
		var lname = $("#lname").val();
		var intid = $("#intid").val();
		var email = $("#email").val();
		var username = $("#username").val();
		var password = $("#password").val();
		var password2 = $("#password2").val();
		var ext = $("#ext").val();
		var pin = $("#pin").val();
		var langs = $("#langs").val();
		var lang = $("#lang").val();
		var level = $("#level").val();
		
		//SET AS AJAX
		jQNC("#ajax").val('1');

		//ERROR VARIABLES
		var fnameError = false;
		var lnameError = false;
		var intidError = false;
		var emailError = false;
		var usernameError = false;
		var passwordError = false;
		var extError = false;
		var pinError = false;
		var langsError = false;
		var langError = false;
		var levelError = false;
        var post_url;

		//TESTS
		var emailReg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        var numericReg = /^[0-9]+$/;
        var alphaReg = /^[a-zA-Z ]+$/;


		//	
		//BEGIN VALIDATION
		//

		
		//FIRST NAME
		if(fname === ""){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter a First Name</div>");
			fnameError = true;
		}
		if(fnameError === true){
            return false; 
        }


        //LAST NAME
		if(lname === ""){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter a Last Name</div>");
			lnameError = true;
		}
		if(lnameError === true){
            return false; 
        }
		
		//INT ID
		if(intid === ""){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter an Interpreter ID</div>");
			intidError = true;
		}
		else if(intid.length != 4){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>Interpreter ID Must have 4 Numbers</div>");
			intidError = true;
		}
		else if(!numericReg.test(intid)){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>Interpreter ID Must Contain Only Numbers</div>");
			intidError = true;
		}
		else if (intid !== ""){
            post_url = "check_intid/" + intid;
            jQNC.ajax({
                type: "POST",
                url: post_url,
                dataType: "json",
                success: function(data){
                    if(data===true){
                        $('#errors').html("<div class='alert error'><span class='hide'>x</span>Interpreter ID Already Exists.</div>");
                        intidError = true;
                    }
                }
            });
        }
		if(intidError === true){
            return false; 
        }

        //EMAIL
		if(email === ''){
            jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter an Email Address.</div>");
            emailError = true;
        } else if(!emailReg.test(email)){
            jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter a Valid Email Address.</div>");
            emailError = true;
        }
        if(emailError === true){
            return false; 
        }

        //USERNAME
        if(username === ""){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter a Username.</div>");
			usernameError = true;
		} else if (username !== ""){
            post_url = "check_username/" + username;
            jQNC.ajax({
                type: "POST",
                url: post_url,
                dataType: "json",
                success: function(data){
                    if(data === true){
                        $('#errors').html("<div class='alert error'><span class='hide'>x</span>Username Already Exists.</div>");
                        usernameError = true;
                    }
                 }
            });
        }
        if(usernameError === true){
            return false; 
        }

        //PASSWORD 
        if (password === '') {
            jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter a Password.</div>");
            passwordError = true;
        } else if (password2 === '') {
            jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Re=Enter the Password.</div>");
            passwordError = true;
        } else if (password != password2 ) {
            jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>The Passwords do not match.</div>");
            passwordError = true;
        }
        if(passwordError === true){
            return false;
        }

        //EXTENSION
        if(ext === ""){
            jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter an Extension.</div>");
             extError = true;
        } else if(ext.length != 4){
            jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>Extension Must have 4 Numbers</div>");
			extError = true;
		} else if(!numericReg.test(ext)){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>Extension Must Contain Only Numbers</div>");
			extError = true;
		} else if (ext !== ""){
            post_url = "check_extension/" + ext;
            jQNC.ajax({
                type: "POST",
                url: post_url,
                dataType: "json",
                success: function(data){
                    if(data === true){
                        $('#errors').html("<div class='alert error'><span class='hide'>x</span>Extension Already Exists.</div>");
                        extError = true;
                    }
                }
            });
        }
        if(extError === true){
            return false;
        }

        //PIN
         if(pin === ""){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter a Pin.</div>");
			pinError = true;
		} else if(pin.length != 4){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>Pin Must have 4 Numbers</div>");
			pinError = true;
		} else if(!numericReg.test(pin)){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>Pin Must Contain Only Numbers</div>");
			pinError = true;
		}
		if(pinError === true){
            return false;
        }
		
		//NUMBER OF LANGUAGES
		if(langs === ""){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Enter the Number of Languages</div>");
			langsError = true;
		}
		else if(langs.length > 1){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Stated Too Many Languages</div>");
			langsError = true;
		}
		else if(!numericReg.test(langs)){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>Spoken Languages Must Contain Only Numbers</div>");
			langsError = true;
		}
		if(langsError === true){
			return false;
		}
		

		//LANGUAGE SELECT
		if(lang === ''){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Select a Primary Language.</div>");
			langError = true;
		}
		if(langError === true){
			return false;
		}
		

		//LEVEL SELECT
		if(level === ''){
			jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Select a Permission Level.</div>");
			levelError = true;
		}
		if(levelError === true){
			return false;
		}
		//	
		//END VALIDATION
		//
		var dataString = jQNC("#userform").serialize();

		jQNC.ajax({
            type: "POST",
            url: "make_user/",
            data: dataString,
            success: function(){
                jQNC('#errors').html("<div class='alert success'><span class='hide'>x</span>User Created.</div>");
                jQNC('#fname').val('');
				jQNC('#lname').val('');
				jQNC('#intid').val('');
				jQNC('#email').val('');
				jQNC('#username').val('');
                jQNC('#password').val('');
				jQNC('#password2').val('');
				jQNC('#ext').val('');
				jQNC('#pin').val('');
				jQNC('#langs').val('');
				jQNC('#lang').val('');
				jQNC('#level').val('');
				jQNC.uniform.update();
				
			}
        });
        return false; 
	});

});