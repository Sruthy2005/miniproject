function startScanner() {
    // Clear any existing scanner
    if (window.html5QrcodeScanner) {
        window.html5QrcodeScanner.clear();
    }

    // Create scanner instance
    window.html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", 
        {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            },
            rememberLastUsedCamera: true,
            showTorchButtonIfSupported: true,
            supportedScanTypes: [
                Html5QrcodeScanType.SCAN_TYPE_CAMERA
            ],
            aspectRatio: 1.0,
            showZoomSliderIfSupported: true,
            defaultZoomValueIfSupported: 2
        }
    );

    // Render the scanner
    window.html5QrcodeScanner.render(onScanSuccess, onScanFailure);
}

function onScanSuccess(decodedText, decodedResult) {
    try {
        // Stop scanning after successful scan
        window.html5QrcodeScanner.pause();
        
        const qrData = JSON.parse(decodedText);
        verifyQRCode(qrData);
    } catch (error) {
        console.error("Failed to parse QR code:", error);
        $('#qr-result').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Invalid QR code format
            </div>
        `);
        // Resume scanning after error
        window.html5QrcodeScanner.resume();
    }
}

function onScanFailure(error) {
    // Only show error if it's not the common "No MultiFormat Readers" error
    if (!error.includes("No MultiFormat Readers")) {
        console.warn(`QR Code scanning failure:`, error);
    }
}

function verifyQRCode(qrData) {
    $('#qr-result').html(`
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin"></i> Verifying QR code...
        </div>
    `);

    fetch('verify_qr.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            appointment_id: currentAppointmentId,
            qr_data: JSON.stringify(qrData)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#qr-result').html(`
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> Verified Successfully!</h5>
                    <p class="mb-0">
                        Client: ${data.client_name}<br>
                        Service: ${data.service}<br>
                        Date: ${data.date}<br>
                        Time: ${data.time}
                    </p>
                </div>
            `);
            setTimeout(() => {
                $('#qrScannerModal').modal('hide');
                location.reload();
            }, 2000);
        } else {
            $('#qr-result').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> ${data.message}
                </div>
            `);
            window.html5QrcodeScanner.resume();
        }
    })
    .catch(error => {
        console.error('Verification error:', error);
        $('#qr-result').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Verification failed. Please try again.
            </div>
        `);
        window.html5QrcodeScanner.resume();
    });
}

// Update the modal HTML
<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="scanner-container">
                    <div id="reader"></div>
                    <div id="qr-result" class="mt-3"></div>
                    <div class="scanning-tips alert alert-info mt-3">
                        <h6><i class="fas fa-info-circle"></i> Scanning Tips:</h6>
                        <ul class="mb-0">
                            <li>Ensure good lighting</li>
                            <li>Hold the QR code steady</li>
                            <li>Keep the QR code within the scanning area</li>
                            <li>Clean your camera lens</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.html5QrcodeScanner.resume()">
                    <i class="fas fa-redo"></i> Retry Scan
                </button>
            </div>
        </div>
    </div>
</div>

// Update modal cleanup
$('#qrScannerModal').on('hidden.bs.modal', function () {
    if (window.html5QrcodeScanner) {
        window.html5QrcodeScanner.stop().then(() => {
            window.html5QrcodeScanner = null;
            $('#qr-result').html('');
        }).catch(error => {
            console.error("Failed to clear scanner:", error);
        });
    }
});

// Add camera selection support
function initializeCameraSelection() {
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            const cameraSelect = document.createElement('select');
            cameraSelect.className = 'form-select mb-3';
            cameraSelect.id = 'camera-select';
            
            devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.id;
                option.text = device.label || `Camera ${devices.indexOf(device) + 1}`;
                cameraSelect.appendChild(option);
            });

            cameraSelect.addEventListener('change', (e) => {
                if (window.html5QrcodeScanner) {
                    window.html5QrcodeScanner.stop().then(() => {
                        startScanner(e.target.value);
                    });
                }
            });

            document.querySelector('#reader').insertAdjacentElement('beforebegin', cameraSelect);
        }
    }).catch(err => {
        console.error("Error getting cameras:", err);
    });
}

// Update the scanQRCode function
function scanQRCode(appointmentId) {
    currentAppointmentId = appointmentId;
    
    // Clear previous results
    $('#qr-result').html('');
    
    // Show the modal
    $('#qrScannerModal').modal('show');
    
    // Initialize camera selection and scanner when modal is shown
    $('#qrScannerModal').on('shown.bs.modal', function() {
        initializeCameraSelection();
        startScanner();
    });
}

// Add some CSS to improve the scanner UI
const style = document.createElement('style');
style.textContent = `
    #reader {
        width: 100% !important;
        border: none !important;
        border-radius: 8px;
        overflow: hidden;
    }
    
    #reader video {
        width: 100% !important;
        border-radius: 8px;
    }
    
    #camera-select {
        max-width: 100%;
        margin-bottom: 1rem;
    }
    
    .alert ul {
        padding-left: 1.2rem;
    }

    .scanner-container {
        max-width: 100%;
        margin: 0 auto;
    }

    #qr-result {
        margin-top: 1rem;
    }

    .scanning-tips {
        font-size: 0.9rem;
    }

    .scanning-tips ul {
        padding-left: 1.2rem;
        margin-top: 0.5rem;
    }

    /* Improve scanner UI */
    #reader__scan_region {
        background: rgba(0,0,0,0.05) !important;
    }

    #reader__scan_region > img {
        max-width: 100%;
        height: auto;
    }

    /* Camera permissions dialog */
    #reader__camera_permission_button {
        padding: 8px 16px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        margin: 10px 0;
    }
`;
document.head.appendChild(style); 