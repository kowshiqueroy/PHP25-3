<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>OMR Sheet A4 Perfect Fit</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;600;700&display=swap');

        /* Force A4 Size and remove browser margins */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        :root {
            --bubble-size: 11px; /* Slightly smaller for better fit */
            --font-size-text: 10px;
            --font-size-bubble: 8px;
            --border-color: #000;
        }

        body, html {
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
            font-family: 'Kalpurush', sans-serif;
            background-color: #fff;
            color: #000;
            overflow: hidden; /* Prevents scrollbars from printing */
        }

        /* The Main 2x2 Grid */
        .page-container {
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: 50% 50%;
            grid-template-rows: 50% 50%;
        }

        /* Individual Section (A5 Size) */
        .section {
            border-right: 1px dashed #999;
            border-bottom: 1px dashed #999;
            padding: 8mm 6mm; /* Margins inside the section */
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            height: 100%;
        }

        /* Remove outer borders for cleaner cut lines */
        .section:nth-child(2n) { border-right: none; }
        .section:nth-child(n+3) { border-bottom: none; }

        /* Header */
        .header {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 5px;
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
            white-space: nowrap;
        }

        /* Top Info Block (Bio + Roll/Set) */
        .top-block {
            display: flex;
            justify-content: space-between;
            height: 48mm; /* Fixed height to reserve space */
            border-bottom: 1px solid #000;
            margin-bottom: 2mm;
            padding-bottom: 2mm;
        }

        /* Left Side: Text Inputs */
        .info-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            font-size: var(--font-size-text);
        }

        .input-row {
            display: flex;
            align-items: flex-end;
        }
        .label {
            font-weight: 600;
            margin-right: 4px;
            white-space: nowrap;
        }
        .line {
            flex-grow: 1;
            border-bottom: 1px dotted #000;
        }

        /* Right Side: OMR Blocks (Set & Roll) */
        .info-right {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .omr-group {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .group-title {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .box-row {
            display: flex;
            gap: 2px;
            margin-bottom: 3px;
        }
        .box {
            width: 16px;
            height: 16px;
            border: 1px solid #000;
        }

        /* OMR Bubbles */
        .bubble-grid-row {
            display: flex;
            gap: 4px; /* Horizontal gap between sets of bubbles */
        }
        .bubble-col {
            display: flex;
            flex-direction: column;
            gap: 1px; /* Vertical gap between bubbles */
        }

        .bubble {
            width: var(--bubble-size);
            height: var(--bubble-size);
            border: 1px solid #000;
            border-radius: 50%;
            font-size: var(--font-size-bubble);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        /* Questions Section */
        .questions-container {
            flex-grow: 1;
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 columns */
            column-gap: 30mm;
            align-content: start;
        }

        .q-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px dotted #ccc;
            padding: 1px 0; /* Very tight padding */
        }

        .q-num {
            font-size: 10px;
            font-weight: bold;
            width: 15px;
        }

        .q-bubbles {
            display: flex;
            gap: 15px;
        }

        /* Helper for print visibility */
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }

    </style>
</head>
<body>

<div class="page-container">
    
    <script>
        // Data helpers
        const bengaliNumerals = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        const toBn = (n) => n.toString().replace(/\d/g, d => bengaliNumerals[d]);
        const options = ['ক','খ','গ','ঘ'];

        // HTML for a single section
        function getSectionHTML() {
            let qLeft = '';
            let qRight = '';

            // Generate Questions 1-15
            for(let i=1; i<=15; i++) {
                qLeft += `
                <div class="q-row">
                    <span class="q-num">${toBn(i)}</span>
                    <div class="q-bubbles">
                        ${options.map(o => `<div class="bubble">${o}</div>`).join('')}
                    </div>
                </div>`;
            }

            // Generate Questions 16-30
            for(let i=16; i<=30; i++) {
                qRight += `
                <div class="q-row">
                    <span class="q-num">${toBn(i)}</span>
                    <div class="q-bubbles">
                        ${options.map(o => `<div class="bubble">${o}</div>`).join('')}
                    </div>
                </div>`;
            }

            // Generate Roll Bubbles (Vertical 0-9 columns)
            let rollColHTML = '';
            for(let i=0; i<=9; i++) {
                rollColHTML += `<div class="bubble">${bengaliNumerals[i]}</div>`;
            }

            return `
            <div class="section">
                <div class="header">পারভেজ খান রেসিডেন্সিয়াল স্কুল অ্যান্ড কলেজ</div>
                
                <div class="top-block">
                    <div class="info-left">
                        <div class="input-row"><span class="label">পরীক্ষা:</span>...............................................................<span class="label">তারিখ:</span>
                        .......................................</div><br>
                        <div class="input-row"><span class="label">শ্রেণি:</span>............................................
                        <span class="label">বিষয়:</span>...............................................................</div><br>
                       
                        <div class="input-row"><span class="label">পরীক্ষকের স্বাক্ষর:</span><div class="line"></div></div><br>
                        <div class="input-row"><span class="label">প্রাপ্ত নম্বর:</span>........................ <span class="label">নিরীক্ষকের স্বাক্ষর:</span>.........................................................</div>
                    </div>

                    <div class="info-right">
                        <div class="omr-group">
                            <span class="group-title">সেট</span>
                            <div class="box-row"><div class="box"></div></div>
                            <div class="bubble-grid-row">
                                <div class="bubble-col">
                                    ${options.map(o => `<div class="bubble">${o}</div>`).join('')}
                                </div>
                            </div>
                        </div>

                        <div class="omr-group">
                            <span class="group-title">রোল</span>
                            <div class="box-row">
                                <div class="box"></div><div class="box"></div>
                            </div>
                            <div class="bubble-grid-row">
                                <div class="bubble-col">${rollColHTML}</div>
                                <div class="bubble-col">${rollColHTML}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="questions-container">
                    <div class="q-col">${qLeft}</div>
                    <div class="q-col">${qRight}</div>
                </div>
            </div>
            `;
        }

        // Write the sections 4 times
        for(let s=0; s<4; s++) {
            document.write(getSectionHTML());
        }
    </script>

</div>

</body>
</html>