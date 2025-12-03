//Online / Offine Status
// function isOnline(){
// 	var jam_offnya = sessionStorage.getItem("offline_time");
	
// 	var currentdate = new Date();
// 	var jam_online = currentdate.getFullYear() + "-" + ("0" + (currentdate.getMonth() + 1)).slice(-2) + "-" + ("0" + (currentdate.getDate() + 1)).slice(-2) + " " + ("0" + (currentdate.getHours() + 1)).slice(-2) + ":" + ("0" + (currentdate.getMinutes() + 1)).slice(-2) + ":" + ("0" + (currentdate.getSeconds() + 1)).slice(-2);
	
// 	$.ajax({
// 		type: "POST",
// 		data: {
// 			downtime: jam_offnya,
// 			uptime: jam_online
// 		},
// 		url: base_url+'penjualan-mobile/log_offline',
// 		success: function(response) {
// 			Swal.fire({
// 				icon: 'success',
// 				title: "Koneksi Internet Kembali",
// 				text: "Koneksi anda sudah kembali, anda dapat melakukan aktivitas kembali!",
// 				showConfirmButton: true
// 			});
// 		}
// 	});
// }

// function isOffline(){		
// 	var currentdate = new Date(); 
// 	var datetime = currentdate.getFullYear() + "-" + ("0" + (currentdate.getMonth() + 1)).slice(-2) + "-" + ("0" + (currentdate.getDate() + 1)).slice(-2) + " " + ("0" + (currentdate.getHours() + 1)).slice(-2) + ":" + ("0" + (currentdate.getMinutes() + 1)).slice(-2) + ":" + ("0" + (currentdate.getSeconds() + 1)).slice(-2);

// 	sessionStorage.setItem("offline_time", datetime);
	
// 	Swal.fire({
// 		icon: 'error',
// 		title: "Koneksi Internet Terputus",
// 		text: "Harap tunggu hingga koneksi anda kembali!",
// 		allowOutsideClick: false,
// 		showConfirmButton: false
// 	});
// }

//Check if Online / Offline
// function updateOnlineStatus(event) {var condition = navigator.onLine ? "online" : "offline"; isOnline(); }
// function updateOfflineStatus(event) {isOffline();}

// window.addEventListener('online',  updateOnlineStatus);
// window.addEventListener('offline', updateOfflineStatus);

function show_alert(title, content, icon, timer) {
	
	message = parse_message(content);
	
	const setting = { 
		title: title,
		html: message,
		icon: icon,
		showConfirmButton : true
	}
	
	if (timer) {
		setting.timer = timer
		setting.showConfirmButton = false
	}
	
	Swal.fire( setting )
}

function generate_alert(type, message) {
	return '<div class="alert alert-dismissible alert-'+type+'" role="alert">' + message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
	
}

function format_date(pattern) {
	var now = new Date();
	var dd = String(now.getDate()).padStart(2, '0');
	var mm = String(now.getMonth() + 1).padStart(2, '0');
	var yyyy = now.getFullYear();
	
	result = pattern.replace('dd', dd);
	result = result.replace('mm', mm);
	result = result.replace('yyyy', yyyy);
	
	return result;
}

function parse_message(content) {
	let message = content;
	if (typeof (content) == 'object') 
	{
		keys = Object.keys(content);
		
		if (keys.length == 1) {
			for (k in content) {
				message = content[k];
			}
		} else {
			message = '<ul>';
			for (k in content) {
				message += '<li>' + content[k] + '</li>';
			}
			message += '</ul>';
		}
	}
	
	return message;
}

function format_ribuan(bilangan, desimal = false) 
{	
	if (!bilangan) {
		return '';
	}
	
	if (typeof bilangan == 'number') {
		bilangan = bilangan.toString().replace(/[.]/g, ',');
	}
	
	bilangan = bilangan.toString().replace(/[^-,\d]/g, '');
	bilangan = bilangan.trim();
		
	if (bilangan.slice(-1) == ',')
	{
		split_number = bilangan.split(',');
		if (split_number.length > 1) {
			
			//trailing comma
			if (split_number[1] == '') {
				bilangan = bilangan.replace(/,*$/, "") + ',';
			} else {
				// Multi Comma
				bilangan = bilangan.replace(/,*$/, "");
			}
		}
		return bilangan;
	}
	
	if (bilangan == '-')
		return 0;
	
	split_bilangan = bilangan.split(',');
	koma = '';
	if (desimal) {
		if (split_bilangan.length > 1) {
			koma = split_bilangan[1];
			koma = ',' + koma.toString().replace(/\D/g, '');
			bilangan = split_bilangan[0];
			
			// Dua angka dibelakang koma
			if (split_bilangan[1].length > 2) {
				dummy = '1.' + split_bilangan[1];
				dummy = parseFloat(dummy);
				if (dummy > 1) {
					dummy = dummy.toFixed(2);
					koma = ',' + dummy.toString().split('.')[1];
				}
			}
		}
		
		bilangan = parseFloat( bilangan );
	} else {
		bilangan = bilangan.replace(',','.');
		bilangan = parseFloat( bilangan );
		bilangan = Math.round( bilangan );
	}
	

	let minus = bilangan.toString().substr(0,1) == '-' ? '-' : '';
	
	var	reverse = bilangan.toString().split('').reverse().join(''),
		ribuan 	= reverse.match(/\d{1,3}/g);
		ribuan	= ribuan.join('.').split('').reverse().join('');
		
	if (desimal) 
		return minus + ribuan + koma;
	
	return minus + ribuan;
}

function setInt (number) 
{
	if (!number)
		return 0;
	
	number = number.toString().replace(/[^-,\d]/g, '');
	split = number.split(',');
	if (split.length > 1) {
		number = split[0] + '.' + split[1];
	}
	
	number = parseFloat(number);

	if (!number)
		return 0;
	
	number = number.toFixed(2);
	number = parseFloat(number);
	// console.log(typeof number);
	return number;
}

function show_toast(message, type = null) {
	
	if (!type) 
		type = 'success';
	
	background = type = 'success' ? 'bg-success' : 'bg-danger';
	const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 2500,
				timerProgressBar: true,
				iconColor: 'white',
				customClass: {
					popup: background + ' text-light toast p-2'
				},
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer)
					toast.addEventListener('mouseleave', Swal.resumeTimer)
				}
			})
			
	Toast.fire({
		html: '<div class="toast-content d-flex"><i class="far fa-check-circle me-2 mt-1"></i>' + message + '</div>'
	});
}