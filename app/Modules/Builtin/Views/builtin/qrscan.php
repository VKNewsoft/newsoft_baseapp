<div style="width: 500px" id="reader"></div>
<script>
	let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: {width: 250, height: 250} },
    /* verbose= */ false);
    // html5QrcodeScanner.render(onScanSuccess, onScanFailure);

    html5QrcodeScanner.start(
    0, 
    {
        fps: 10,    // Optional, frame per seconds for qr code scanning
        qrbox: { width: 250, height: 250 }  // Optional, if you want bounded box UI
    },
    (decodedText, decodedResult) => {
        // do something when code is read
    },
    (errorMessage) => {
        // parse error, ignore it.
    })
    .catch((err) => {
    // Start failed, handle it.
    });
</script>