function idfParseFloat(num) {
	return parseFloat(num.replace(/[^\d.-]/g, ''));
}

function idfPriceFormat(price) {
	formattedPrice = price.toFixed(2);
	return formattedPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function idfStripUrlQuery(url) {
	return url.split("?")[0];
}

function idfDatePickerFormat() {
	var date;
	switch(idf_date_format) {
		case 'F j, Y':
			date = 'MM dd, yy';
			break;
		case 'jS F Y':
			date = 'dd MM yy';
			break;
		case 'Y-m-d':
			date = 'yy-mm-dd';
			break;
		case 'm/d/Y':
			date = 'mm/dd/yy';
			break;
		case 'd/m/Y':
			date = 'dd/mm/yy';
			break;
		default:
			// use default format
			date = 'mm/dd/yy';
			break;
	}
	return date;
}

function idfValidateEmail(email) {
    var validate = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return validate.test(email);
	// "to avoid syntax color changing for now. #RemoveIt
}