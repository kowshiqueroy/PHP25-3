<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visual C Code Generator</title>

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

        function handleDragStart(e) {
            draggedElement = e.target;
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/html', e.target.outerHTML);
        }

        function handleBlockClick(e) {
            const blockType = e.target.dataset.type;
            addBlock(blockType, document.getElementById('dropZone'));
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        }

        function handleDragEnter(e) {
            e.preventDefault();
            if (e.target.classList.contains('drop-zone') || e.target.classList.contains('nested-container')) {
                e.target.classList.add('drag-over');
            }
        }

        function handleDragLeave(e) {
            if (e.target.classList.contains('drop-zone') || e.target.classList.contains('nested-container')) {
                e.target.classList.remove('drag-over');
            }
        }

        function handleDrop(e) {
            e.preventDefault();
            const target = e.target.closest('.drop-zone, .nested-container');
            if (target) {
                target.classList.remove('drag-over');
                const blockType = draggedElement.dataset.type;
                addBlock(blockType, target);
            }
        }

        function addBlock(blockType, container) {
            const blockId = `block_${blockCounter++}`;
            const template = blockTemplates[blockType];
            
            if (!template) return;

            const blockDiv = document.createElement('div');
            blockDiv.className = 'dragged-block';
            blockDiv.dataset.type = blockType;
            blockDiv.dataset.id = blockId;
            blockDiv.draggable = true;
            blockDiv.innerHTML = `
                ${template.html}
                <div class="block-controls">
                    <button class="control-btn" onclick="moveUp(this)">↑</button>
                    <button class="control-btn" onclick="moveDown(this)">↓</button>
                    <button class="control-btn" onclick="deleteBlock(this)">×</button>
                </div>
            `;

            // Add drag events for repositioning
            blockDiv.addEventListener('dragstart', handleBlockDragStart);
            blockDiv.addEventListener('dragover', handleBlockDragOver);
            blockDiv.addEventListener('drop', handleBlockDrop);
            blockDiv.addEventListener('dragenter', handleBlockDragEnter);
            blockDiv.addEventListener('dragleave', handleBlockDragLeave);

            container.appendChild(blockDiv);
            
            // Initialize nested containers
            const nestedContainers = blockDiv.querySelectorAll('.nested-container');
            nestedContainers.forEach(nested => {
                nested.addEventListener('dragover', handleDragOver);
                nested.addEventListener('drop', handleDrop);
                nested.addEventListener('dragenter', handleDragEnter);
                nested.addEventListener('dragleave', handleDragLeave);
            });

            updateCode();
            showToast('Block added successfully!');
        }

        function handleBlockD ragStart(e) {
            draggedElement = e.target;
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/html', e.target.outerHTML);
        }

        function handleBlockDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';

        }

        function handleBlockDrop(e) {
            e.preventDefault();
            const target = e.target.closest('.nested-container');
            if (target) {
                target.classList.remove('drag-over');
                const blockType = draggedElement.dataset.type;
                addBlock(blockType, target);
            }
        }

        function handleBlockDragEnter(e) {
            e.preventDefault();
            if (e.target.classList.contains('nested-container')) {
                e.target.classList.add('drag-over');
            }
        }

        function handleBlockDragLeave(e) {
            if (e.target.classList.contains('nested-container')) {
                e.target.classList.remove('drag-over');
            }
        }

        function moveUp(blockElement) {
            const blockId = blockElement.closest('.dragged-block').dataset.id;
            const blockIndex = blocks.findIndex(block => block.id === blockId);
            if (blockIndex > 0) {
                [blocks[blockIndex], blocks[blockIndex - 1]] = [blocks[blockIndex - 1], blocks[blockIndex]];
                updateCode();
            }
        }

        function moveDown(blockElement) {
            const blockId = blockElement.closest('.dragged-block').dataset.id;
            const blockIndex = blocks.findIndex(block => block.id === blockId);
            if (blockIndex < blocks.length - 1) {
                [blocks[blockIndex], blocks[blockIndex + 1]] = [blocks[blockIndex + 1], blocks[blockIndex]];
                updateCode();
            }
        }

        function deleteBlock(blockElement) {
            const blockId = blockElement.closest('.dragged-block').dataset.id;
            const blockIndex = blocks.findIndex(block => block.id === blockId);
            if (blockIndex !== -1) {
                blocks.splice(blockIndex, 1);
                updateCode();
            }
        }

        function updateCode() {
            const codeContainer = document.getElementById('code');
            codeContainer.innerHTML = '';
            blocks.forEach(block => {
                const code = blockTemplates[block.type].code(block.inputs);
                const codeDiv = document.createElement('div');
                codeDiv.className = 'code-block';
                codeDiv.innerHTML = code;
                codeContainer.appendChild(codeDiv);
            });
        }

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>