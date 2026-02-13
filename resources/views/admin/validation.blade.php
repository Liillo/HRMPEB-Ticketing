@extends('layouts.admin')

@section('title', 'Ticket Validation')

@push('styles')
<script src="https://unpkg.com/html5-qrcode"></script>
@endpush

@section('content')
<h1 style="color: var(--color-primary); margin-bottom: 32px;">
    <i class="fas fa-qrcode"></i> Ticket Validation
</h1>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <h2 style="color: var(--color-primary); margin-bottom: 24px; text-align: center;">
            Scan Ticket QR Code
        </h2>
        
        <div id="result-message" style="display: none; padding: 16px; border-radius: 8px; margin-bottom: 20px;"></div>
        
        <div id="ticket-info" style="display: none; background: var(--color-muted); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="color: var(--color-primary); margin-bottom: 16px;">
                <i class="fas fa-ticket-alt"></i> Ticket Information
            </h3>
            <div id="ticket-details"></div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <button id="start-scan-btn" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;" onclick="startScanner()">
                <i class="fas fa-camera"></i> Start Camera Scanner
            </button>
            <button id="stop-scan-btn" class="btn btn-danger" style="width: 100%; display: none; margin-top: 10px;" onclick="stopScanner()">
                <i class="fas fa-stop"></i> Stop Scanner
            </button>
        </div>
        
        <div id="qr-reader" style="display: none; margin-bottom: 20px; border-radius: 8px; overflow: hidden;"></div>
        
        <div style="text-align: center; margin: 20px 0; color: var(--text-secondary);">
            <strong>OR</strong>
        </div>
        
        <div class="form-group">
            <label for="qr-input">
                <i class="fas fa-keyboard"></i> Enter Ticket UUID or Scan with Barcode Scanner
            </label>
            <input type="text" id="qr-input" placeholder="Paste UUID or use barcode scanner" autofocus>
            <small style="display: block; margin-top: 8px; color: var(--text-secondary);">
                <i class="fas fa-info-circle"></i> Barcode scanners work automatically - just scan!
            </small>
        </div>
        
        <button type="button" class="btn btn-secondary" style="width: 100%;" onclick="validateTicketManual()">
            <i class="fas fa-check"></i> Validate Ticket
        </button>
    </div>
</div>

@push('scripts')
<script>
let html5QrCode;
let lastScan = '';
let scanTimeout;
let isScanning = false;
let processingScan = false;

// Auto-submit when barcode scanner inputs data
document.getElementById('qr-input').addEventListener('input', function(e) {
    clearTimeout(scanTimeout);
    scanTimeout = setTimeout(() => {
        if (e.target.value && e.target.value !== lastScan) {
            lastScan = e.target.value;
            validateTicketManual();
        }
    }, 500);
});

async function startScanner() {
    if (isScanning) {
        return;
    }

    document.getElementById('start-scan-btn').style.display = 'none';
    document.getElementById('stop-scan-btn').style.display = 'block';
    document.getElementById('qr-reader').style.display = 'block';

    html5QrCode = new Html5Qrcode('qr-reader');

    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };

    try {
        const cameraConfig = await getPreferredCameraConfig();
        await html5QrCode.start(
            cameraConfig,
            config,
            onScanSuccess,
            onScanError
        );
        isScanning = true;
    } catch (err) {
        console.error('Camera error:', err);
        alert('Error accessing camera: ' + err);
        stopScanner();
    }
}

function stopScanner() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop().then(() => {
            isScanning = false;
            document.getElementById('start-scan-btn').style.display = 'block';
            document.getElementById('stop-scan-btn').style.display = 'none';
            document.getElementById('qr-reader').style.display = 'none';
        }).catch(err => {
            console.error('Error stopping scanner:', err);
        });
    } else {
        document.getElementById('start-scan-btn').style.display = 'block';
        document.getElementById('stop-scan-btn').style.display = 'none';
        document.getElementById('qr-reader').style.display = 'none';
    }
}

async function onScanSuccess(decodedText) {
    if (!decodedText || processingScan || decodedText === lastScan) {
        return;
    }

    processingScan = true;
    lastScan = decodedText;

    let uuid = decodedText;
    if (decodedText.includes('/')) {
        uuid = decodedText.split('/').pop();
    }

    document.getElementById('qr-input').value = uuid;
    await validateTicket(uuid);

    if (navigator.vibrate) {
        navigator.vibrate(120);
    }

    setTimeout(() => {
        lastScan = '';
        processingScan = false;
        document.getElementById('qr-input').value = '';
    }, 1200);
}

function onScanError(error) {
    // Silently ignore scan errors
}

function validateTicketManual() {
    const qrCode = document.getElementById('qr-input').value.trim();
    if (!qrCode) {
        showError('Please enter a ticket UUID or scan QR code');
        return;
    }
    validateTicket(qrCode);
}

function validateTicket(qrCode) {
    const resultDiv = document.getElementById('result-message');
    const ticketInfoDiv = document.getElementById('ticket-info');
    const ticketDetailsDiv = document.getElementById('ticket-details');

    resultDiv.style.display = 'none';

    return fetch('{{ route('admin.scan') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ qr_code: qrCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            ticketInfoDiv.style.display = 'block';

            let statusBadge = '';
            if (data.ticket.type === 'corporate') {
                if (data.ticket.scan_count >= data.ticket.max_scans) {
                    statusBadge = '<span class="scan-badge scanned"><i class="fas fa-check-double"></i> All Attendees In</span>';
                } else {
                    statusBadge = '<span class="scan-badge partial"><i class="fas fa-user-check"></i> ' + data.ticket.scan_count + '/' + data.ticket.max_scans + ' In Event</span>';
                }
            } else {
                statusBadge = '<span class="scan-badge scanned"><i class="fas fa-check"></i> Scanned In</span>';
            }

            ticketDetailsDiv.innerHTML = `
                <div style="margin-bottom: 12px;">
                    <strong>Type:</strong> ${data.ticket.type.charAt(0).toUpperCase() + data.ticket.type.slice(1)}
                </div>
                <div style="margin-bottom: 12px;">
                    <strong>Name/Company:</strong> ${data.ticket.name}
                </div>
                <div style="margin-bottom: 12px;">
                    <strong>Status:</strong> ${statusBadge}
                </div>
                ${data.ticket.type === 'corporate' ? '<div><strong>Remaining Scans:</strong> ' + data.ticket.remaining_scans + '</div>' : ''}
            `;

            setTimeout(() => {
                ticketInfoDiv.style.display = 'none';
            }, 5000);
        } else {
            showError(data.message);
            ticketInfoDiv.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Validation error:', error);
        showError('Error validating ticket. Please try again.');
        ticketInfoDiv.style.display = 'none';
    });
}

async function getPreferredCameraConfig() {
    const cameras = await Html5Qrcode.getCameras();

    if (!cameras || cameras.length === 0) {
        return { facingMode: { exact: 'environment' } };
    }

    const rearCamera = cameras.find(camera =>
        /back|rear|environment|traseira|arriere/i.test(camera.label || '')
    );

    const selectedCamera = rearCamera || cameras[0];
    return { deviceId: { exact: selectedCamera.id } };
}

function showSuccess(message) {
    const resultDiv = document.getElementById('result-message');
    resultDiv.style.display = 'block';
    resultDiv.style.background = '#d4edda';
    resultDiv.style.color = '#155724';
    resultDiv.style.border = '1px solid #c3e6cb';
    resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;

    setTimeout(() => {
        resultDiv.style.display = 'none';
    }, 5000);
}

function showError(message) {
    const resultDiv = document.getElementById('result-message');
    resultDiv.style.display = 'block';
    resultDiv.style.background = '#f8d7da';
    resultDiv.style.color = '#721c24';
    resultDiv.style.border = '1px solid #f5c6cb';
    resultDiv.innerHTML = '<i class="fas fa-times-circle"></i> ' + message;

    setTimeout(() => {
        resultDiv.style.display = 'none';
    }, 5000);
}
</script>
@endpush
@endsection
