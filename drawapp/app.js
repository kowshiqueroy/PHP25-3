document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('drawingCanvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const sidebar = document.getElementById('sidebar');
    const sidebarToggleButton = document.getElementById('sidebarToggleButton');
    const cameraView = document.getElementById('cameraView');
    const cameraToggleButton = document.getElementById('cameraToggleButton');
    const closeCameraButton = document.getElementById('closeCameraButton');

    const webcamFeed = document.getElementById('webcamFeed');
    const noCameraMessage = document.getElementById('noCameraMessage');

    // Tool elements
    const pencilTool = document.getElementById('pencilTool');
    const highlighterTool = document.getElementById('highlighterTool');
    const markerTool = document.getElementById('markerTool');
    const eraserTool = document.getElementById('eraserTool');
    const toolButtons = document.querySelectorAll('.tool-button');

    // Color elements
    const colorBoxes = document.querySelectorAll('.color-box');
    const colorPicker = document.getElementById('colorPicker');

    // Pen size elements
    const penSizeSlider = document.getElementById('penSizeSlider');
    const penSizeValue = document.getElementById('penSizeValue');

    // Canvas action elements
    const undoButton = document.getElementById('undoButton');
    const clearButton = document.getElementById('clearButton');

    // Canvas navigation elements
    const prevCanvasButton = document.getElementById('prevCanvasButton');
    const nextCanvasButton = document.getElementById('nextCanvasButton');
    const newCanvasButton = document.getElementById('newCanvasButton');
    const canvasPageIndex = document.getElementById('canvasPageIndex');

    // Camera transparency
    const cameraTransparencySlider = document.getElementById('cameraTransparencySlider');
    const cameraTransparencyValue = document.getElementById('cameraTransparencyValue');

    // Save options
    const savePdfButton = document.getElementById('savePdfButton');

    let isDrawing = false;
    let currentTool = 'pencil'; // Default tool
    let currentColor = '#000000'; // Default color
    let currentPenSize = 2; // Default pen size

    // Multi-canvas management
    const MAX_CANVASES = 30;
    let canvases = []; // Stores ImageData for each canvas
    let currentCanvasIndex = 0;

    // Undo/Redo history for current canvas
    let undoStack = [];
    let redoStack = [];
    const MAX_HISTORY = 20; // Limit history size

    // Helper function to convert hex color to RGBA
    const hexToRgba = (hex, alpha) => {
        let r = 0, g = 0, b = 0;
        // Handle #RGB format
        if (hex.length === 4) {
            r = parseInt(hex[1] + hex[1], 16);
            g = parseInt(hex[2] + hex[2], 16);
            b = parseInt(hex[3] + hex[3], 16);
        }
        // Handle #RRGGBB format
        else if (hex.length === 7) {
            r = parseInt(hex.substring(1, 3), 16);
            g = parseInt(hex.substring(3, 5), 16);
            b = parseInt(hex.substring(5, 7), 16);
        }
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };

    // Function to save current canvas state to undo stack
    const saveState = () => {
        redoStack = []; // Clear redo stack on new action
        if (undoStack.length >= MAX_HISTORY) {
            undoStack.shift(); // Remove oldest state if stack is full
        }
        undoStack.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
    };

    // Function to restore canvas state
    const restoreState = (imageData) => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.putImageData(imageData, 0, 0);
    };

    // Function to update canvas page indicator
    const updateCanvasPageIndex = () => {
        canvasPageIndex.textContent = `${currentCanvasIndex + 1}/${canvases.length}`;
        prevCanvasButton.disabled = currentCanvasIndex === 0;
        nextCanvasButton.disabled = currentCanvasIndex === canvases.length - 1;
        newCanvasButton.disabled = canvases.length >= MAX_CANVASES;
    };

    // Function to switch to a specific canvas
    const switchToCanvas = (index) => {
        if (index >= 0 && index < canvases.length) {
            // Save current canvas state before switching
            canvases[currentCanvasIndex] = ctx.getImageData(0, 0, canvas.width, canvas.height);

            currentCanvasIndex = index;
            restoreState(canvases[currentCanvasIndex]);
            undoStack = []; // Reset undo/redo for new canvas
            redoStack = [];
            saveState(); // Save initial state of new canvas
            updateCanvasPageIndex();
        }
    };

    // Function to add a new canvas
    const addNewCanvas = () => {
        if (canvases.length < MAX_CANVASES) {
            // Save current canvas state before creating new one
            canvases[currentCanvasIndex] = ctx.getImageData(0, 0, canvas.width, canvas.height);

            const newCanvasData = ctx.createImageData(canvas.width, canvas.height);
            canvases.push(newCanvasData);
            currentCanvasIndex = canvases.length - 1;
            restoreState(canvases[currentCanvasIndex]);
            undoStack = []; // Reset undo/redo for new canvas
            redoStack = [];
            saveState(); // Save initial state of new canvas
            updateCanvasPageIndex();
        }
    };

    // Initial canvas setup
    const resizeCanvas = () => {
        const aspectRatio = 297 / 210; // A4 landscape ratio (width / height)
        const containerWidth = window.innerWidth;
        const containerHeight = window.innerHeight;

        let newWidth = containerWidth;
        let newHeight = containerWidth / aspectRatio;

        if (newHeight > containerHeight) {
            newHeight = containerHeight;
            newWidth = containerHeight * aspectRatio;
        }

        canvas.width = newWidth;
        canvas.height = newHeight;

        // Restore current canvas state after resize
        if (canvases[currentCanvasIndex]) {
            restoreState(canvases[currentCanvasIndex]);
        } else {
            // If no canvases exist yet, create the first one
            canvases.push(ctx.createImageData(canvas.width, canvas.height));
            saveState(); // Save initial state
        }
        updateCanvasPageIndex();

        // Apply current drawing properties
        ctx.lineWidth = currentPenSize;
        ctx.lineCap = 'round';
        ctx.strokeStyle = currentColor;
        ctx.globalCompositeOperation = currentTool === 'eraser' ? 'destination-out' : 'source-over';
    };

    // Initial resize and resize on window change
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    // Drawing functions for main canvas
    const startDrawing = (e) => {
        e.preventDefault(); // Prevent default browser actions
        isDrawing = true;
        // Auto-hide sidebar on drawing start
        if (sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
        }
        ctx.beginPath();
        ctx.moveTo(e.clientX - canvas.getBoundingClientRect().left, e.clientY - canvas.getBoundingClientRect().top);

        // Set initial line width based on current tool and pen size
        // This ensures the first stroke segment has the correct base width
        switch (currentTool) {
            case 'pencil':
                ctx.lineWidth = currentPenSize;
                break;
            case 'highlighter':
                ctx.lineWidth = currentPenSize * 10;
                break;
            case 'marker':
                ctx.lineWidth = currentPenSize * 3;
                break;
            case 'eraser':
                ctx.lineWidth = currentPenSize * 15;
                break;
        }
    };

    const draw = (e) => {
        if (!isDrawing) return;

        let effectiveLineWidth = ctx.lineWidth; // Start with the base line width set by the tool

        // Adjust line width based on pressure if available
        if (e.pressure !== undefined && e.pressure > 0) {
            // Modulate the effectiveLineWidth based on pressure
            effectiveLineWidth = currentPenSize * e.pressure * 2; // Use currentPenSize as base for pressure modulation
        }

        // Apply the effectiveLineWidth for the current drawing segment
        ctx.lineWidth = effectiveLineWidth;

        if (currentTool === 'marker') {
            // Draw spray dots for marker
            const sprayRadius = ctx.lineWidth * 1.5; // Radius of the spray area
            const numDots = 100; // Number of dots per spray segment

            for (let i = 0; i < numDots; i++) {
                const offsetX = (Math.random() - 0.5) * sprayRadius * 2;
                const offsetY = (Math.random() - 0.5) * sprayRadius * 2;
                ctx.beginPath();
                ctx.arc(e.clientX - canvas.getBoundingClientRect().left + offsetX, e.clientY - canvas.getBoundingClientRect().top + offsetY, ctx.lineWidth / 2, 0, Math.PI * 2);
                ctx.fill();
            }
        } else {
            ctx.lineTo(e.clientX - canvas.getBoundingClientRect().left, e.clientY - canvas.getBoundingClientRect().top);
            ctx.stroke();
        }
    };

    const stopDrawing = () => {
        if (isDrawing) {
            isDrawing = false;
            ctx.closePath();
            saveState(); // Save state after each drawing stroke
        }
    };

    // Event listeners for main canvas
    canvas.addEventListener('pointerdown', startDrawing);
    canvas.addEventListener('pointermove', draw);
    canvas.addEventListener('pointerup', stopDrawing);
    canvas.addEventListener('pointerout', stopDrawing); // Stop drawing if pointer leaves canvas

    // Sidebar toggle
    sidebarToggleButton.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });

    // Camera view toggle
    cameraToggleButton.addEventListener('click', () => {
        cameraView.classList.toggle('open');
        if (cameraView.classList.contains('open')) {
            cameraToggleButton.style.display = 'none'; // Hide the button
            // Attempt to start webcam feed
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then((stream) => {
                        webcamFeed.srcObject = stream;
                        webcamFeed.style.display = 'block'; // Show video
                        noCameraMessage.style.display = 'none'; // Hide message
                    })
                    .catch((error) => {
                        console.error('Error accessing webcam:', error);
                        webcamFeed.style.display = 'none'; // Hide video
                        noCameraMessage.style.display = 'flex'; // Show message
                        alert('Could not access webcam. Please ensure you have a camera connected and have granted permission.');
                        // cameraView.classList.remove('open'); // Keep camera view open to show message
                        // cameraToggleButton.style.display = 'flex'; // Don't show button, message is there
                    });
            } else {
                webcamFeed.style.display = 'none'; // Hide video
                noCameraMessage.style.display = 'flex'; // Show message
                alert('Your browser does not support webcam access.');
                // cameraView.classList.remove('open'); // Keep camera view open to show message
                // cameraToggleButton.style.display = 'flex'; // Don't show button, message is there
            }
        }
    });

    // Close camera button
    closeCameraButton.addEventListener('click', () => {
        cameraView.classList.remove('open');
        cameraToggleButton.style.display = 'flex'; // Show the button
        if (webcamFeed.srcObject) {
            webcamFeed.srcObject.getTracks().forEach(track => track.stop());
            webcamFeed.srcObject = null;
        }
        webcamFeed.style.display = 'block'; // Reset to default for next open
        noCameraMessage.style.display = 'none'; // Hide message
    });

    // Function to update canvas cursor
    const updateCanvasCursor = () => {
        console.log('Updating cursor for tool:', currentTool);
        // Remove all existing cursor classes
        canvas.classList.remove(
            'canvas-pencil-cursor',
            'canvas-highlighter-cursor',
            'canvas-marker-cursor',
            'canvas-eraser-cursor'
        );

        // Add the class for the current tool
        const newCursorClass = `canvas-${currentTool}-cursor`;
        canvas.classList.add(newCursorClass);
        console.log('Added class:', newCursorClass, 'Current canvas classes:', canvas.classList);
    };

    // Function to update sidebar button icon and color
    const updateSidebarButtonIconAndColor = () => {
        const sidebarIcon = sidebarToggleButton.querySelector('i');
        if (!sidebarIcon) return;

        // Remove existing tool icon classes
        sidebarIcon.classList.remove(
            'fa-pencil-alt',
            'fa-highlighter',
            'fa-marker',
            'fa-eraser'
        );

        // Set icon based on current tool
        switch (currentTool) {
            case 'pencil':
                sidebarIcon.classList.add('fa-pencil-alt');
                break;
            case 'highlighter':
                sidebarIcon.classList.add('fa-highlighter');
                break;
            case 'marker':
                sidebarIcon.classList.add('fa-marker');
                break;
            case 'eraser':
                sidebarIcon.classList.add('fa-eraser');
                break;
            default:
                sidebarIcon.classList.add('fa-bars'); // Fallback to bars icon
        }

        // Set icon color based on current color (unless it's eraser)
        if (currentTool !== 'eraser') {
            sidebarIcon.style.color = currentColor;
        } else {
            sidebarIcon.style.color = 'white'; // Eraser icon remains white
        }
    };

    // Tool selection logic
    toolButtons.forEach(button => {
        button.addEventListener('click', () => {
            toolButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            currentTool = button.id.replace('Tool', '');

            ctx.lineCap = 'round'; // Always round line cap

            // Set properties based on tool
            switch (currentTool) {
                case 'pencil':
                    currentPenSize = 2; // Set default pen size for pencil
                    penSizeSlider.value = currentPenSize; // Update slider
                    penSizeValue.textContent = currentPenSize; // Update displayed value
                    ctx.lineWidth = currentPenSize;
                    ctx.globalAlpha = 1; // Fully opaque
                    ctx.globalCompositeOperation = 'source-over';
                    currentColor = '#000000'; // Set default pencil color to black
                    colorPicker.value = currentColor; // Update color picker
                    // Deactivate other color boxes and activate black if it exists
                    colorBoxes.forEach(box => {
                        if (box.dataset.color === '#000000') {
                            box.classList.add('active');
                        } else {
                            box.classList.remove('active');
                        }
                    });
                    break;
                case 'highlighter':
                    currentPenSize = 15; // Set default pen size for highlighter
                    penSizeSlider.value = currentPenSize; // Update slider
                    penSizeValue.textContent = currentPenSize; // Update displayed value
                    ctx.lineWidth = currentPenSize * 10; // Bigger stroke
                    ctx.globalAlpha = 1; // Transparency handled by rgba color
                    ctx.globalCompositeOperation = 'multiply'; // For highlighter effect
                    currentColor = '#FFFF00'; // Set default highlighter color to yellow
                    colorPicker.value = currentColor; // Update color picker
                    // Deactivate other color boxes and activate yellow if it exists
                    colorBoxes.forEach(box => {
                        if (box.dataset.color === '#FFFF00') {
                            box.classList.add('active');
                        } else {
                            box.classList.remove('active');
                        }
                    });
                    break;
                case 'marker':
                    currentPenSize = 1; // Set default pen size for marker
                    penSizeSlider.value = currentPenSize; // Update slider
                    penSizeValue.textContent = currentPenSize; // Update displayed value
                    ctx.lineWidth = currentPenSize * 3;
                    ctx.globalAlpha = 1; // Fully opaque
                    ctx.globalCompositeOperation = 'source-over';
                    currentColor = '#FF0000'; // Set default marker color to red
                    colorPicker.value = currentColor; // Update color picker
                    // Deactivate other color boxes and activate red if it exists
                    colorBoxes.forEach(box => {
                        if (box.dataset.color === '#FF0000') {
                            box.classList.add('active');
                        } else {
                            box.classList.remove('active');
                        }
                    });
                    break;
                case 'eraser':
                    currentPenSize = 40; // Set base pen size for eraser
                    penSizeSlider.value = currentPenSize; // Update slider
                    penSizeValue.textContent = currentPenSize; // Update displayed value
                    ctx.lineWidth = currentPenSize * 100; // Eraser size is 100 times pen size
                    ctx.globalAlpha = 1; // Eraser is opaque
                    ctx.globalCompositeOperation = 'destination-out';
                    break;
            }
            // Apply current color, unless it's the eraser
            if (currentTool !== 'eraser') {
                ctx.strokeStyle = currentColor;
                ctx.fillStyle = currentColor; // For marker dots
            } else {
                // Eraser effectively uses transparent color, but strokeStyle needs to be set
                ctx.strokeStyle = 'rgba(0,0,0,1)';
                ctx.fillStyle = 'rgba(0,0,0,1)';
            }
            updateCanvasCursor(); // Update cursor after tool selection
            updateSidebarButtonIconAndColor(); // Update sidebar button icon and color
        });
    });

    // Initial active tool
    pencilTool.classList.add('active');
    updateCanvasCursor(); // Set initial cursor
    updateSidebarButtonIconAndColor(); // Set initial sidebar button icon and color

    // Color selection logic
    colorBoxes.forEach(box => {
        box.addEventListener('click', () => {
            colorBoxes.forEach(b => b.classList.remove('active'));
            box.classList.add('active');
            currentColor = box.dataset.color;
            colorPicker.value = currentColor;
            // Apply color with current tool's alpha
            if (currentTool !== 'eraser') {
                ctx.strokeStyle = currentColor;
                ctx.fillStyle = currentColor; // For marker dots
            }
            updateSidebarButtonIconAndColor(); // Update sidebar button icon and color
        });
    });

    colorPicker.addEventListener('input', (e) => {
        currentColor = e.target.value;
        colorBoxes.forEach(b => b.classList.remove('active')); // Deactivate preset colors
        // Apply color with current tool's alpha
        if (currentTool !== 'eraser') {
            ctx.strokeStyle = currentColor;
            ctx.fillStyle = currentColor; // For marker dots
        }
        updateSidebarButtonIconAndColor(); // Update sidebar button icon and color
    });

    // Initial active color
    colorBoxes[0].classList.add('active');

    // Pen size slider logic
    penSizeSlider.addEventListener('input', (e) => {
        currentPenSize = parseInt(e.target.value);
        penSizeValue.textContent = currentPenSize;
        // Update current tool's line width immediately
        switch (currentTool) {
            case 'pencil':
                ctx.lineWidth = currentPenSize;
                break;
            case 'highlighter':
                ctx.lineWidth = currentPenSize * 10;
                break;
            case 'marker':
                ctx.lineWidth = currentPenSize * 3;
                break;
            case 'eraser':
                ctx.lineWidth = currentPenSize * 150;
                break;
        }
    });

    // Undo and Clear functionality
    undoButton.addEventListener('click', () => {
        if (undoStack.length > 1) { // Keep at least one state (the current one before undo)
            const lastState = undoStack.pop();
            redoStack.push(lastState);
            restoreState(undoStack[undoStack.length - 1]);
        } else if (undoStack.length === 1) {
            // If only one state left, it means the initial blank canvas. Clear it.
            redoStack.push(undoStack.pop());
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // Optionally, push a truly blank state if you want to be able to redo back to it
            // undoStack.push(ctx.createImageData(canvas.width, canvas.height));
        }
    });

    clearButton.addEventListener('click', () => {
        if (confirm('Are you sure you want to clear the current canvas?')) {
            saveState(); // Save current state before clearing for undo
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // After clearing, the current state is now blank, so update the last state in undoStack
            undoStack[undoStack.length - 1] = ctx.getImageData(0, 0, canvas.width, canvas.height);
        }
    });

    // Canvas navigation
    prevCanvasButton.addEventListener('click', () => {
        switchToCanvas(currentCanvasIndex - 1);
    });

    nextCanvasButton.addEventListener('click', () => {
        switchToCanvas(currentCanvasIndex + 1);
    });

    newCanvasButton.addEventListener('click', addNewCanvas);

    // Camera transparency slider logic
    cameraTransparencySlider.addEventListener('input', (e) => {
        const transparency = parseInt(e.target.value);
        cameraTransparencyValue.textContent = transparency;
        cameraView.style.opacity = transparency / 100;
    });

    // Initial camera transparency
    cameraView.style.opacity = cameraTransparencySlider.value / 100;

    // Save as PDF functionality
    savePdfButton.addEventListener('click', async () => {
        if (typeof window.jspdf === 'undefined') {
            alert('jsPDF library not loaded. Please check your internet connection or script tag.');
            return;
        }

        // Ensure the very latest drawing on the current canvas is saved
        canvases[currentCanvasIndex] = ctx.getImageData(0, 0, canvas.width, canvas.height);

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4'
        });

        // Temporarily save the current canvas state (this is now redundant, but harmless)
        const originalCanvasData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const originalCanvasIndex = currentCanvasIndex;

        // Create a temporary canvas for generating image data URLs
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        const tempCtx = tempCanvas.getContext('2d');

        for (let i = 0; i < canvases.length; i++) {
            // Ensure a white background for the temporary canvas
            tempCtx.fillStyle = 'white';
            tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);

            // Draw the stored ImageData onto the temporary canvas
            // We need to create a new canvas from ImageData to use drawImage
            const imgCanvas = document.createElement('canvas');
            imgCanvas.width = tempCanvas.width;
            imgCanvas.height = tempCanvas.height;
            const imgCtx = imgCanvas.getContext('2d');
            imgCtx.putImageData(canvases[i], 0, 0); // Put ImageData onto this intermediate canvas

            // Now draw this intermediate canvas onto the tempCanvas
            tempCtx.drawImage(imgCanvas, 0, 0);

            // Convert temporary canvas to image data URL
            const imgData = tempCanvas.toDataURL('image/jpeg', 0.8); // Use JPEG for smaller file size

            // Add page to PDF
            if (i > 0) {
                pdf.addPage();
            }
            pdf.addImage(imgData, 'JPEG', 0, 0, pdf.internal.pageSize.getWidth(), pdf.internal.pageSize.getHeight());
        }

        // Restore the original canvas state
        restoreState(originalCanvasData);
        switchToCanvas(originalCanvasIndex); // Ensure the correct canvas is active

        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const filename = `KR${year}${month}${day}_${hours}${minutes}${seconds}.pdf`;
        pdf.save(filename);
    });

    const drawingCanvas1 = document.getElementById('drawingCanvas1');
    const drawingCanvas2 = document.getElementById('drawingCanvas2');

    [drawingCanvas1, drawingCanvas2].forEach(canvas => {
        canvas.height = window.innerHeight;
    });

    // Generic drawing setup function for additional canvases
    const setupAdditionalCanvasDrawing = (canvas, ctx) => { // Removed Ref parameters
        let isDrawingLocal = false; // Local drawing state for this canvas

        const startDrawingLocal = (e) => {
            e.preventDefault();
            isDrawingLocal = true;
            ctx.beginPath();
            const rect = canvas.getBoundingClientRect();
            ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);

            // Set initial line width based on current tool and pen size
            switch (currentTool) { // Changed to currentTool
                case 'pencil':
                    ctx.lineWidth = currentPenSize; // Changed to currentPenSize
                    break;
                case 'highlighter':
                    ctx.lineWidth = currentPenSize * 10; // Changed to currentPenSize
                    break;
                case 'marker':
                    ctx.lineWidth = currentPenSize * 3; // Changed to currentPenSize
                    break;
                case 'eraser':
                    ctx.lineWidth = currentPenSize * 100; // Changed to currentPenSize
                    break;
            }
            ctx.strokeStyle = currentTool === 'eraser' ? 'rgba(0,0,0,1)' : currentColor; // Changed to currentTool, currentColor
            ctx.fillStyle = currentTool === 'eraser' ? 'rgba(0,0,0,1)' : currentColor; // Changed to currentTool, currentColor
            ctx.globalCompositeOperation = currentTool === 'eraser' ? 'destination-out' : 'source-over'; // Changed to currentTool
            ctx.globalAlpha = currentTool === 'highlighter' ? 0.5 : 1; // Changed to currentTool
        };

        const drawLocal = (e) => {
            if (!isDrawingLocal) return;

            let effectiveLineWidth = ctx.lineWidth;
            if (e.pressure !== undefined && e.pressure > 0) {
                effectiveLineWidth = currentPenSize * e.pressure * 2; // Changed to currentPenSize
            }
            ctx.lineWidth = effectiveLineWidth;

            if (currentTool === 'marker') { // Changed to currentTool
                const sprayRadius = ctx.lineWidth * 1.5;
                const numDots = 100;

                for (let i = 0; i < numDots; i++) {
                    const offsetX = (Math.random() - 0.5) * sprayRadius * 2;
                    const offsetY = (Math.random() - 0.5) * sprayRadius * 2;
                    const rect = canvas.getBoundingClientRect();
                    ctx.beginPath();
                    ctx.arc(e.clientX - rect.left + offsetX, e.clientY - rect.top + offsetY, ctx.lineWidth / 2, 0, Math.PI * 2);
                    ctx.fill();
                }
            } else {
                const rect = canvas.getBoundingClientRect();
                ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
                ctx.stroke();
            }
        };

        const stopDrawingLocal = () => {
            if (isDrawingLocal) {
                isDrawingLocal = false;
                ctx.closePath();
                // No saveState for these canvases as they don't have undo/redo
            }
        };

        canvas.addEventListener('pointerdown', startDrawingLocal);
        canvas.addEventListener('pointermove', drawLocal);
        canvas.addEventListener('pointerup', stopDrawingLocal);
        canvas.addEventListener('pointerout', stopDrawingLocal);
    };

    [drawingCanvas1, drawingCanvas2].forEach(canvas => {
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = false;
        setupAdditionalCanvasDrawing(canvas, ctx); // Removed Ref parameters
    });
});




