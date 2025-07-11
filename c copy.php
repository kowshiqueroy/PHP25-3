<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visual C Code Generator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            color: #333;
        }

        .container {
            display: flex;
            width: 100%;
            height: 100vh;
            gap: 10px;
            padding: 10px;
        }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow-y: auto;
        }

        .sidebar h3 {
            margin-bottom: 15px;
            color: #4a5568;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }

        .block-group {
            margin-bottom: 20px;
        }

        .block-item {
            background: linear-gradient(135deg, #4299e1, #3182ce);
            color: white;
            padding: 12px 16px;
            margin: 8px 0;
            border-radius: 8px;
            cursor: grab;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            user-select: none;
            position: relative;
            overflow: hidden;
        }

        .block-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(66, 153, 225, 0.4);
        }

        .block-item:active {
            cursor: grabbing;
            transform: scale(0.98);
        }

        .block-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .block-item:hover::before {
            transform: translateX(100%);
        }

        .workspace {
            flex: 1;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow-y: auto;
        }

        .workspace h3 {
            margin-bottom: 15px;
            color: #4a5568;
            font-size: 18px;
            font-weight: 600;
        }

        .drop-zone {
            min-height: 400px;
            border: 3px dashed #cbd5e0;
            border-radius: 12px;
            padding: 20px;
            background: rgba(247, 250, 252, 0.8);
            transition: all 0.3s ease;
            position: relative;
        }

        .drop-zone.drag-over {
            border-color: #4299e1;
            background: rgba(66, 153, 225, 0.1);
            transform: scale(1.02);
        }

        .drop-zone:empty::before {
            content: 'Drop code blocks here or click blocks from the sidebar to add them';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #a0aec0;
            font-size: 16px;
            text-align: center;
            pointer-events: none;
        }

        .code-output {
            flex: 1;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow-y: auto;
        }

        .code-output h3 {
            margin-bottom: 15px;
            color: #4a5568;
            font-size: 18px;
            font-weight: 600;
        }

        .code-display {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 14px;
            line-height: 1.6;
            overflow-x: auto;
            white-space: pre-wrap;
            min-height: 300px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dragged-block {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 12px 16px;
            margin: 8px 0;
            border-radius: 8px;
            cursor: move;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            user-select: none;
            position: relative;
            border-left: 4px solid #2f855a;
        }

        .dragged-block:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 16px rgba(72, 187, 120, 0.3);
        }

        .dragged-block.dragging {
            opacity: 0.7;
            transform: rotate(5deg);
        }

        .nested-container {
            margin-left: 20px;
            margin-top: 10px;
            border-left: 2px solid #e2e8f0;
            padding-left: 15px;
            min-height: 30px;
        }

        .nested-container.drag-over {
            border-left-color: #4299e1;
            background: rgba(66, 153, 225, 0.05);
        }

        .block-input {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin: 0 8px;
            font-size: 13px;
            min-width: 80px;
            transition: all 0.3s ease;
        }

        .block-input:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .block-controls {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .dragged-block:hover .block-controls {
            opacity: 1;
        }

        .control-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            color: white;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .control-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .copy-btn {
            background: linear-gradient(135deg, #9f7aea, #805ad5);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-top: 15px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(159, 122, 234, 0.4);
        }

        .copy-btn:active {
            transform: scale(0.98);
        }

        .clear-btn {
            background: linear-gradient(135deg, #f56565, #e53e3e);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-top: 15px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .clear-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(245, 101, 101, 0.4);
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 16px rgba(72, 187, 120, 0.3);
        }

        .toast.show {
            transform: translateX(0);
        }

        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
                height: auto;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                max-height: 300px;
            }
            
            .workspace, .code-output {
                width: 100%;
                min-height: 400px;
            }
        }

        /* Syntax highlighting */
        .keyword { color: #c792ea; }
        .string { color: #c3e88d; }
        .comment { color: #676e95; }
        .number { color: #f78c6c; }
        .function { color: #82aaff; }
        .operator { color: #89ddff; }
        .type { color: #ffcb6b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h3>📚 Code Blocks</h3>
            
            <div class="block-group">
                <h4 style="color: #666; margin-bottom: 10px;">Headers & Definitions</h4>
                <div class="block-item" data-type="include">#include &lt;stdio.h&gt;</div>
                <div class="block-item" data-type="define">#define NAME value</div>
                <div class="block-item" data-type="const">const type name = value;</div>
                <div class="block-item" data-type="global">Global Variable</div>
                <div class="block-item" data-type="struct">struct name { };</div>
            </div>

            <div class="block-group">
                <h4 style="color: #666; margin-bottom: 10px;">Variables & Arrays</h4>
                <div class="block-item" data-type="var_int">int variable;</div>
                <div class="block-item" data-type="var_float">float variable;</div>
                <div class="block-item" data-type="var_char">char variable;</div>
                <div class="block-item" data-type="var_string">char variable[];</div>
                <div class="block-item" data-type="array">type array[size];</div>
            </div>

            <div class="block-group">
                <h4 style="color: #666; margin-bottom: 10px;">Functions</h4>
                <div class="block-item" data-type="main">main() function</div>
                <div class="block-item" data-type="function">Custom Function</div>
                <div class="block-item" data-type="function_call">Function Call</div>
            </div>

            <div class="block-group">
                <h4 style="color: #666; margin-bottom: 10px;">Control Flow</h4>
                <div class="block-item" data-type="if">if statement</div>
                <div class="block-item" data-type="else_if">else if statement</div>
                <div class="block-item" data-type="else">else statement</div>
                <div class="block-item" data-type="for">for loop</div>
                <div class="block-item" data-type="while">while loop</div>
                <div class="block-item" data-type="do_while">do-while loop</div>
            </div>

            <div class="block-group">
                <h4 style="color: #666; margin-bottom: 10px;">Input/Output</h4>
                <div class="block-item" data-type="printf">printf statement</div>
                <div class="block-item" data-type="scanf">scanf statement</div>
                <div class="block-item" data-type="printf_var">printf with variables</div>
            </div>

            <div class="block-group">
                <h4 style="color: #666; margin-bottom: 10px;">Operators</h4>
                <div class="block-item" data-type="assignment">Assignment</div>
                <div class="block-item" data-type="arithmetic">Arithmetic</div>
                <div class="block-item" data-type="comparison">Comparison</div>
                <div class="block-item" data-type="logical">Logical</div>
            </div>

            <button class="clear-btn" onclick="clearWorkspace()">
                🗑️ Clear Workspace
            </button>
        </div>

        <div class="workspace">
            <h3>🔨 Code Builder</h3>
            <div class="drop-zone" id="dropZone">
                <!-- Dragged blocks will appear here -->
            </div>
        </div>

        <div class="code-output">
            <h3>💻 Generated C Code</h3>
            <div class="code-display" id="codeDisplay">
// Your generated C code will appear here
#include &lt;stdio.h&gt;

int main() {
    // Start building your code!
    return 0;
}
            </div>
            <button class="copy-btn" onclick="copyCode()">
                📋 Copy to Clipboard
            </button>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        let draggedElement = null;
        let blockCounter = 0;
        let blocks = [];

        // Block templates
        const blockTemplates = {
            include: {
                html: '#include &lt;<input type="text" class="block-input" value="stdio.h" onchange="updateCode()">&gt;',
                code: (inputs) => `#include <${inputs[0] || 'stdio.h'}>`
            },
            define: {
                html: '#define <input type="text" class="block-input" value="NAME" onchange="updateCode()"> <input type="text" class="block-input" value="value" onchange="updateCode()">',
                code: (inputs) => `#define ${inputs[0] || 'NAME'} ${inputs[1] || 'value'}`
            },
            const: {
                html: 'const <input type="text" class="block-input" value="int" onchange="updateCode()"> <input type="text" class="block-input" value="name" onchange="updateCode()"> = <input type="text" class="block-input" value="value" onchange="updateCode()">;',
                code: (inputs) => `const ${inputs[0] || 'int'} ${inputs[1] || 'name'} = ${inputs[2] || 'value'};`
            },
            global: {
                html: '<input type="text" class="block-input" value="int" onchange="updateCode()"> <input type="text" class="block-input" value="globalVar" onchange="updateCode()"> = <input type="text" class="block-input" value="0" onchange="updateCode()">;',
                code: (inputs) => `${inputs[0] || 'int'} ${inputs[1] || 'globalVar'} = ${inputs[2] || '0'};`
            },
            struct: {
                html: 'struct <input type="text" class="block-input" value="name" onchange="updateCode()"> {<div class="nested-container" data-accepts="var_int,var_float,var_char,var_string,array"></div>};',
                code: (inputs, nested) => `struct ${inputs[0] || 'name'} {\n${nested.map(n => '    ' + n).join('\n')}\n};`
            },
            var_int: {
                html: 'int <input type="text" class="block-input" value="variable" onchange="updateCode()"> = <input type="text" class="block-input" value="0" onchange="updateCode()">;',
                code: (inputs) => `int ${inputs[0] || 'variable'} = ${inputs[1] || '0'};`
            },
            var_float: {
                html: 'float <input type="text" class="block-input" value="variable" onchange="updateCode()"> = <input type="text" class="block-input" value="0.0" onchange="updateCode()">;',
                code: (inputs) => `float ${inputs[0] || 'variable'} = ${inputs[1] || '0.0'};`
            },
            var_char: {
                html: 'char <input type="text" class="block-input" value="variable" onchange="updateCode()"> = <input type="text" class="block-input" value="\'a\'" onchange="updateCode()">;',
                code: (inputs) => `char ${inputs[0] || 'variable'} = ${inputs[1] || "'a'"};`
            },
            var_string: {
                html: 'char <input type="text" class="block-input" value="variable" onchange="updateCode()">[] = <input type="text" class="block-input" value="\\"hello\\"" onchange="updateCode()">;',
                code: (inputs) => `char ${inputs[0] || 'variable'}[] = ${inputs[1] || '"hello"'};`
            },
            array: {
                html: '<input type="text" class="block-input" value="int" onchange="updateCode()"> <input type="text" class="block-input" value="array" onchange="updateCode()">[<input type="text" class="block-input" value="10" onchange="updateCode()">];',
                code: (inputs) => `${inputs[0] || 'int'} ${inputs[1] || 'array'}[${inputs[2] || '10'}];`
            },
            main: {
                html: 'int main(<input type="text" class="block-input" value="" placeholder="argc, argv" onchange="updateCode()">) {<div class="nested-container" data-accepts="all"></div>return <input type="text" class="block-input" value="0" onchange="updateCode()">; }',
                code: (inputs, nested) => `int main(${inputs[0] || ''}) {\n${nested.map(n => '    ' + n).join('\n')}\n    return ${inputs[1] || '0'};\n}`
            },
            function: {
                html: '<input type="text" class="block-input" value="void" onchange="updateCode()"> <input type="text" class="block-input" value="function" onchange="updateCode()">(<input type="text" class="block-input" value="" placeholder="parameters" onchange="updateCode()">) {<div class="nested-container" data-accepts="all"></div>}',
                code: (inputs, nested) => `${inputs[0] || 'void'} ${inputs[1] || 'function'}(${inputs[2] || ''}) {\n${nested.map(n => '    ' + n).join('\n')}\n}`
            },
            function_call: {
                html: '<input type="text" class="block-input" value="function" onchange="updateCode()">(<input type="text" class="block-input" value="" placeholder="arguments" onchange="updateCode()">);',
                code: (inputs) => `${inputs[0] || 'function'}(${inputs[1] || ''});`
            },
            if: {
                html: 'if (<input type="text" class="block-input" value="condition" onchange="updateCode()">) {<div class="nested-container" data-accepts="all"></div>}',
                code: (inputs, nested) => `if (${inputs[0] || 'condition'}) {\n${nested.map(n => '    ' + n).join('\n')}\n}`
            },
            else_if: {
                html: 'else if (<input type="text" class="block-input" value="condition" onchange="updateCode()">) {<div class="nested-container" data-accepts="all"></div>}',
                code: (inputs, nested) => `else if (${inputs[0] || 'condition'}) {\n${nested.map(n => '    ' + n).join('\n')}\n}`
            },
            else: {
                html: 'else {<div class="nested-container" data-accepts="all"></div>}',
                code: (inputs, nested) => `else {\n${nested.map(n => '    ' + n).join('\n')}\n}`
            },
            for: {
                html: 'for (<input type="text" class="block-input" value="int i = 0" onchange="updateCode()">; <input type="text" class="block-input" value="i < 10" onchange="updateCode()">; <input type="text" class="block-input" value="i++" onchange="updateCode()">) {<div class="nested-container" data-accepts="all"></div>}',
                code: (inputs, nested) => `for (${inputs[0] || 'int i = 0'}; ${inputs[1] || 'i < 10'}; ${inputs[2] || 'i++'}) {\n${nested.map(n => '    ' + n).join('\n')}\n}`
            },
            while: {
                html: 'while (<input type="text" class="block-input" value="condition" onchange="updateCode()">) {<div class="nested-container" data-accepts="all"></div>}',
                code: (inputs, nested) => `while (${inputs[0] || 'condition'}) {\n${nested.map(n => '    ' + n).join('\n')}\n}`
            },
            do_while: {
                html: 'do {<div class="nested-container" data-accepts="all"></div>} while (<input type="text" class="block-input" value="condition" onchange="updateCode()">);',
                code: (inputs, nested) => `do {\n${nested.map(n => '    ' + n).join('\n')}\n} while (${inputs[0] || 'condition'});`
            },
            printf: {
                html: 'printf("<input type="text" class="block-input" value="Hello, World!\\n" onchange="updateCode()">");',
                code: (inputs) => `printf("${inputs[0] || 'Hello, World!\\n'}");`
            },
            scanf: {
                html: 'scanf("<input type="text" class="block-input" value="%d" onchange="updateCode()">", <input type="text" class="block-input" value="&variable" onchange="updateCode()">);',
                code: (inputs) => `scanf("${inputs[0] || '%d'}", ${inputs[1] || '&variable'});`
            },
            printf_var: {
                html: 'printf("<input type="text" class="block-input" value="Value: %d\\n" onchange="updateCode()">", <input type="text" class="block-input" value="variable" onchange="updateCode()">);',
                code: (inputs) => `printf("${inputs[0] || 'Value: %d\\n'}", ${inputs[1] || 'variable'});`
            },
            assignment: {
                html: '<input type="text" class="block-input" value="variable" onchange="updateCode()"> = <input type="text" class="block-input" value="value" onchange="updateCode()">;',
                code: (inputs) => `${inputs[0] || 'variable'} = ${inputs[1] || 'value'};`
            },
            arithmetic: {
                html: '<input type="text" class="block-input" value="result" onchange="updateCode()"> = <input type="text" class="block-input" value="a" onchange="updateCode()"> <input type="text" class="block-input" value="+" onchange="updateCode()"> <input type="text" class="block-input" value="b" onchange="updateCode()">;',
                code: (inputs) => `${inputs[0] || 'result'} = ${inputs[1] || 'a'} ${inputs[2] || '+'} ${inputs[3] || 'b'};`
            },
            comparison: {
                html: '<input type="text" class="block-input" value="a" onchange="updateCode()"> <input type="text" class="block-input" value="==" onchange="updateCode()"> <input type="text" class="block-input" value="b" onchange="updateCode()">',
                code: (inputs) => `${inputs[0] || 'a'} ${inputs[1] || '=='} ${inputs[2] || 'b'}`
            },
            logical: {
                html: '<input type="text" class="block-input" value="condition1" onchange="updateCode()"> <input type="text" class="block-input" value="&&" onchange="updateCode()"> <input type="text" class="block-input" value="condition2" onchange="updateCode()">',
                code: (inputs) => `${inputs[0] || 'condition1'} ${inputs[1] || '&&'} ${inputs[2] || 'condition2'}`
            }
        };

        // Initialize drag and drop
        function initializeDragAndDrop() {
            const blockItems = document.querySelectorAll('.block-item');
            const dropZone = document.getElementById('dropZone');

            blockItems.forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('click', handleBlockClick);
                item.draggable = true;
            });

            dropZone.addEventListener('dragover', handleDragOver);
            dropZone.addEventListener('drop', handleDrop);
            dropZone.addEventListener('dragenter', handleDragEnter);
            dropZone.addEventListener('dragleave', handleDragLeave);
        }

        function handleBlockDragStart(e) {
    draggedElement = e.target;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', draggedElement.dataset.id);
}

function handleBlockDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

function handleBlockDrop(e) {
    e.preventDefault();
    const target = e.target.closest('.dragged-block');
    if (!target || draggedElement === target) return;

    const dropZone = target.parentNode;
    const draggedIndex = Array.from(dropZone.children).indexOf(draggedElement);
    const targetIndex = Array.from(dropZone.children).indexOf(target);

    if (draggedIndex < targetIndex) {
        dropZone.insertBefore(draggedElement, target.nextSibling);
    } else {
        dropZone.insertBefore(draggedElement, target);
    }

    updateCode();
}

function handleBlockDragEnter(e) {
    e.preventDefault();
    if (e.target.classList.contains('dragged-block')) {
        e.target.classList.add('drag-over');
    }
}

function handleBlockDragLeave(e) {
    if (e.target.classList.contains('dragged-block')) {
        e.target.classList.remove('drag-over');
    }
}

function moveUp(button) {
    const block = button.closest('.dragged-block');
    const prev = block.previousElementSibling;
    if (prev) {
        block.parentNode.insertBefore(block, prev);
        updateCode();
    }
}

function moveDown(button) {
    const block = button.closest('.dragged-block');
    const next = block.nextElementSibling;
    if (next) {
        block.parentNode.insertBefore(next, block);
        updateCode();
    }
}

function deleteBlock(button) {
    const block = button.closest('.dragged-block');
    block.remove();
    updateCode();
}

function getCodeFromContainer(container) {
    const blocks = container.querySelectorAll(':scope > .dragged-block');
    return Array.from(blocks).map(block => {
        const type = block.dataset.type;
        const template = blockTemplates[type];
        const inputs = Array.from(block.querySelectorAll('.block-input')).map(input => input.value);

        const nested = [];
        block.querySelectorAll(':scope > .nested-container').forEach(nestedContainer => {
            nested.push(getCodeFromContainer(nestedContainer));
        });

        if (template.code.length === 2) {
            return template.code(inputs, nested.flat());
        } else {
            return template.code(inputs);
        }
    });
}

function updateCode() {
    const dropZone = document.getElementById('dropZone');
    const codeLines = getCodeFromContainer(dropZone);
    document.getElementById('codeOutput').textContent = codeLines.join('\n');
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', initializeDragAndDrop);

    </script>
</body>
</html>