function serializeForm(form){
	var count, count2, queue;
	if (!form || form.nodeName !== "FORM") {
		return;
	}
	count = count2 = void 0;
	queue = [];
	count = form.elements.length - 1;
	while (count >= 0) {
	    if (form.elements[count].name === "") {
			count = count - 1;
			continue;
	    }
	    switch (form.elements[count].nodeName) {
	    	case "INPUT":
		        switch (form.elements[count].type) {
					case "text":
					case "hidden":
					case "password":
					case "button":
					case "reset":
					case "submit":
						queue.push(form.elements[count].name + "=" + encodeURIComponent(form.elements[count].value));
						break;
					case "checkbox":
					case "radio":
						if (form.elements[count].checked) {
							queue.push(form.elements[count].name + "=" + encodeURIComponent(form.elements[count].value));
					    }
					    break;
					case "file":
					    break;
				}
	        break;

	      	case "TEXTAREA":
	        	queue.push(form.elements[count].name + "=" + encodeURIComponent(form.elements[count].value));
	        break;

	      	case "SELECT":
	        	switch (form.elements[count].type) {
	          		case "select-one":
			            queue.push(form.elements[count].name + "=" + encodeURIComponent(form.elements[count].value));
			            break;
	          	case "select-multiple":
		            count2 = form.elements[count].options.length - 1;
		            while (count2 >= 0) {
		            	if (form.elements[count].options[count2].selected) {
		                	queue.push(form.elements[count].name + "=" + encodeURIComponent(form.elements[count].options[count2].value));
		            	}
		            	count2 = count2 - 1;
		            }
	        	}
	        break;

	    	case "BUTTON":
		        switch (form.elements[count].type) {
					case "reset":
					case "submit":
					case "button":
			        	queue.push(form.elements[count].name + "=" + encodeURIComponent(form.elements[count].value));
		    	}
		}
		count = count - 1;
	}
	return queue.join("&");
}