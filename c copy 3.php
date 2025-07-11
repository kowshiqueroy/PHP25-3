<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visual C Code Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .toolbox, .workspace {
            border: 2px dashed #ccc;
            min-height: 400px;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .code-block {
            padding: 5px 10px;
            margin: 5px 0;
            background-color: #e9ecef;
            border-radius: 4px;
            cursor: grab;
        }
        .nested {
            margin-left: 20px;
        }
        textarea#code-output {
            font-family: monospace;
            width: 100%;
            height: 300px;
        }
        .editable {
            border: none;
            background: transparent;
            border-bottom: 1px dashed #000;
            min-width: 60px;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <h1 class="text-center mb-4">Visual C Code Generator</h1>
    <div class="row">
        <div class="col-md-4">
            <h4>Toolbox</h4>
            <div id="toolbox" class="toolbox">
                <!-- Toolbox items populated via PHP -->
                <?php
                $blocks = [
                    '#include <stdio.h>',
                    'Global Variable: int <input class="editable" value="x">;',
                    '#define <input class="editable" value="SIZE"> 10',
                    'const int <input class="editable" value="MAX"> = 100;',
                    'struct <input class="editable" value="Person"> { char name[50]; int age; };',
                    'main(int argc, char *argv[])',
                    'Function: int <input class="editable" value="add">(int a, int b)',
                    'Call Function: <input class="editable" value="add">(1, 2);',
                    'For Loop',
                    'While Loop',
                    'Do While Loop',
                    'If Statement',
                    'Else If Statement',
                    'Else Statement',
                    'printf("Value: %d", <input class="editable" value="x">);',
                    'scanf("%d", &<input class="editable" value="x">);',
                    'Variable: int <input class="editable" value="x"> = 0;',
                    'Array: int <input class="editable" value="arr">[10];'
                ];
                foreach ($blocks as $block) {
                    echo "<div class='code-block' draggable='true'>" . $block . "</div>";
                }
                ?>
            </div>
        </div>
        <div class="col-md-4">
            <h4>Workspace</h4>
            <div id="workspace" class="workspace"></div>
        </div>
        <div class="col-md-4">
            <h4>Generated Code</h4>
            <textarea id="code-output" readonly></textarea>
        </div>
    </div>
</div>

<script>
    const toolbox = document.getElementById("toolbox");
    const workspace = document.getElementById("workspace");
    const codeOutput = document.getElementById("code-output");

    let draggedBlock = null;

    document.querySelectorAll('.code-block').forEach(block => {
        block.addEventListener('dragstart', (e) => {
            draggedBlock = block.cloneNode(true);
            enableEditableInputs(draggedBlock);
        });

        block.addEventListener('click', () => {
            const cloned = block.cloneNode(true);
            cloned.setAttribute("draggable", "true");
            workspace.appendChild(cloned);
            enableEditableInputs(cloned);
            updateCode();
            addBlockListeners(cloned);
        });
    });

    workspace.addEventListener("dragover", (e) => e.preventDefault());
    workspace.addEventListener("drop", (e) => {
        if (draggedBlock) {
            draggedBlock.setAttribute("draggable", "true");
            workspace.appendChild(draggedBlock);
            enableEditableInputs(draggedBlock);
            addBlockListeners(draggedBlock);
            updateCode();
            draggedBlock = null;
        }
    });

    function enableEditableInputs(block) {
        block.querySelectorAll('.editable').forEach(input => {
            input.addEventListener('input', updateCode);
        });
    }

    function addBlockListeners(block) {
        block.addEventListener('dragstart', (e) => {
            draggedBlock = block;
        });
        block.addEventListener('click', () => {
            // Add future logic for customization modal if needed
        });
    }

    function updateCode() {
        let code = '';
        workspace.querySelectorAll('.code-block').forEach(block => {
            let line = '';
            block.childNodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    line += node.textContent;
                } else if (node.nodeName === 'INPUT') {
                    line += node.value;
                }
            });
            code += line + '\n';
        });
        codeOutput.value = code;
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
