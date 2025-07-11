<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LEGO Logic → C Code Generator</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body { background: #f0f2f5; }
    .box {
      padding: 10px;
      margin: 5px;
      color: #fff;
      border-radius: 5px;
      position: relative;
      cursor: move;
    }
    .normal  { background-color: #0d6efd; }
    .mother  { background-color: #198754; }
    .child   { background-color: #6f42c1; }
    .hybrid  { background-color: #fd7e14; }
    .child-box {
      background-color: rgba(0,0,0,0.2);
      margin-top: 5px;
      padding: 4px 6px;
      border-radius: 3px;
      position: relative;
    }
    .dropzone, .inner-dropzone {
      min-height: 100px;
      border: 2px dashed #ccc;
      padding: 10px;
      background-color: #ffffff;
      margin-top: 10px;
    }
    .inner-dropzone {
      border: 1px dashed #555;
      background-color: #f6f6f6;
    }
    .edit-value {
      width: 40px;
      margin-left: 8px;
      border-radius: 4px;
      padding: 2px;
      text-align: center;
      font-weight: bold;
      color: #000;
    }
    .control-btn {
      position: absolute;
      top: 4px;
      right: 4px;
      font-size: 0.8em;
      cursor: pointer;
      color: #fff;
      background: rgba(0,0,0,0.4);
      border: none;
      border-radius: 3px;
      padding: 2px 4px;
    }
    pre {
      background-color: #e9ecef;
      padding: 15px;
      border-radius: 5px;
      white-space: pre-wrap;
    }
  </style>
</head>
<body class="p-4">
<div class="container-fluid">
  <div class="row">
    <div class="col-md-4">
      <h5>Palette</h5>
      <div id="palette">
        <div class="box normal" data-type="normal" data-value="5">Normal Box</div>
        <div class="box mother" data-type="mother" data-start="10">Mother Box</div>
        <div class="box child" data-type="child" data-value="3">Child Box</div>
        <div class="box hybrid" data-type="hybrid" data-start="7">Hybrid Box</div>
      </div>
    </div>
    <div class="col-md-4">
      <h5>Canvas</h5>
      <div id="canvas" class="dropzone" ondrop="handleDrop(event)" ondragover="allowDrop(event)"></div>
    </div>
    <div class="col-md-4">
      <h5>Generated C Code</h5>
      <pre><code id="output"></code></pre>
    </div>
  </div>
</div>

<script>
let dragged;

document.querySelectorAll("#palette .box").forEach(el => {
  el.setAttribute("draggable", "true");
  el.ondragstart = e => { dragged = el.cloneNode(true); };
});

function allowDrop(e) { e.preventDefault(); }

function handleDrop(e) {
  e.preventDefault();
  const target = e.target.closest(".dropzone, .inner-dropzone");
  if (!target) return;

  const type = dragged.dataset.type;
  const targetIsCanvas = target.id === "canvas";

  if (!targetIsCanvas) {
    const parentType = target.parentElement.dataset.type;
    if (!["mother", "hybrid"].includes(parentType)) return;
  }

  const box = createBox(dragged);
  target.appendChild(box);
  updateOutput();
}

function createBox(template) {
  const type = template.dataset.type;
  const value = template.dataset.value;
  const start = template.dataset.start;

  const box = document.createElement("div");
  box.className = type === "child" ? "child-box" : `box ${type} mb-2 text-white`;
  box.dataset.type = type;

  if (value) box.dataset.value = value;
  if (start) box.dataset.start = start;

  const label = document.createElement("span");
  label.textContent = template.textContent;

  const input = document.createElement("input");
  input.type = "number";
  input.value = value || start || 0;
  input.className = "edit-value";
  input.oninput = () => {
    if (["mother", "hybrid"].includes(type)) box.dataset.start = input.value;
    else box.dataset.value = input.value;
    updateOutput();
  };

  const delBtn = document.createElement("button");
  delBtn.className = "control-btn";
  delBtn.textContent = "✕";
  delBtn.onclick = () => { box.remove(); updateOutput(); };

  box.appendChild(label);
  box.appendChild(input);
  box.appendChild(delBtn);
  box.setAttribute("draggable", "true");
  box.ondragstart = e => { dragged = box; };

  if (["mother", "hybrid"].includes(type)) {
    const inner = document.createElement("div");
    inner.className = "inner-dropzone";
    inner.ondragover = allowDrop;
    inner.ondrop = handleDrop;
    box.appendChild(inner);
  }

  return box;
}

function updateOutput() {
  let funcCounter = 0, varCounter = 0;
  const declaredVars = new Set();

  const indent = (str, level = 1) => '  '.repeat(level) + str;

  const generateCCode = (box, level = 1) => {
    const type = box.dataset.type;
    let code = "";

    if (["mother", "hybrid"].includes(type)) {
      const funcName = `${type}_func_${++funcCounter}`;
      const start = parseInt(box.dataset.start) || 0;
      const inner = box.querySelector(".inner-dropzone");
      const children = inner ? Array.from(inner.children) : [];
      const childCode = children.map(child => generateCCode(child, 2)).join("\n");

      code += `int ${funcName}() {\n`;
      code += indent(`int result = ${start};`, 1) + "\n";
      if (type === "hybrid") {
        code += indent(`for(int i=0; i<3; i++) {\n${childCode}\n${indent('}', 1)}\n`, 1);
      } else {
        code += childCode + "\n";
      }
      code += indent(`return result;`, 1) + "\n";
      code += `}\n`;
    } else if (type === "child") {
      const val = parseInt(box.dataset.value) || 0;
      code += indent(`result += ${val};`, level);
    } else if (type === "normal") {
      const val = parseInt(box.dataset.value) || 0;
      const varName = `var_${++varCounter}`;
      if (!declaredVars.has(varName)) {
        declaredVars.add(varName);
        code += indent(`int ${varName} = ${val};`, level);
      }
    }

    return code;
  };

  const topBoxes = document.querySelectorAll("#canvas > .box");
  const logic = Array.from(topBoxes).map(box => generateCCode(box)).join("\n\n");

  const structDef = `typedef struct {\n  int id;\n  char name[50];\n} Item;\n`;
  const calls = Array.from({ length: funcCounter }, (_, i) =>
    indent(`printf("Result from func_${i + 1}: %d\\n", ${["mother", "hybrid"].includes(topBoxes[i]?.dataset.type) ? `${topBoxes[i].dataset.type}_func_${i + 1}()` : "0"});`)
  ).join("\n");

  const mainFunc = `int main() {\n${calls}\n  return 0;\n}`;

  const fullCode = `#include <stdio.h>\n#include <string.h>\n\n${structDef}\n\n${logic}\n\n${mainFunc}`;
  document.getElementById("output").textContent = fullCode;
}
</script>
</body>
</html>