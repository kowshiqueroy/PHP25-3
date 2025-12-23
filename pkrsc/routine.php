<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Routine Generator</title>
    <style>
        /* 1. General Styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        h1, h2, h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        h1 { text-align: center; }

        /* 2. Controls & Inputs */
        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
            border-bottom: 2px solid #eee;
            padding-bottom: 25px;
        }
        .control-group {
            flex: 1;
            min-width: 250px;
            background: #fafafa;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .control-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .control-group input, .control-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box; /* Important for 100% width */
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        #generate-btn {
            width: 100%;
            padding: 15px;
            font-size: 1.1em;
            font-weight: 700;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #generate-btn:hover { background-color: #2980b9; }

        /* 3. Output Tables */
        .output-section { display: none; /* Hidden by default */ }
        .routine-grid, .stats-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        .routine-grid th, .routine-grid td,
        .stats-grid th, .stats-grid td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .routine-grid th {
            background-color: #ecf0f1;
            position: sticky;
            top: 0; /* Sticky headers for scrolling */
        }
        /* Color Coding */
        .slot {
            padding: 8px;
            border-radius: 4px;
            text-align: center;
            font-size: 0.85em;
            line-height: 1.4;
        }
        .slot strong { font-size: 1.1em; display: block; }
        .slot.tiffin { background: #fdfae1; color: #725a03; }
        .slot.fallback { background: #fbeae9; color: #9c2b2b; }
        .slot-sci { background: #e8f5e9; color: #2e7d32; }
        .slot-hum { background: #e3f2fd; color: #1565c0; }
        .slot-com { background: #fff3e0; color: #ef6c00; }
        .slot-gen { background: #eceff1; color: #37474f; }

        .table-wrapper {
            max-height: 600px;
            overflow: auto;
            margin-top: 15px;
        }
        .stats-bar {
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            height: 10px;
        }
        .stats-bar-inner {
            background: #4caf50;
            height: 10px;
        }
        .stats-bar-inner.overload { background: #f44336; }

        /* 4. Responsive & Print */
        @media (max-width: 768px) {
            .controls { flex-direction: column; }
            .routine-grid th, .routine-grid td { padding: 5px; font-size: 0.8em; }
            .slot { font-size: 0.75em; padding: 4px; }
        }
        
        @media print {
            body { padding: 0; margin: 0; }
            .container {
                box-shadow: none;
                border: none;
                padding: 0;
            }
            .controls, #generate-btn { display: none; } /* Hide controls when printing */
            .output-section { display: block !important; } /* Show output */
            .table-wrapper { max-height: none; overflow: visible; }
            .routine-grid { font-size: 8pt; }
            .routine-grid th, .routine-grid td { padding: 4px; }
            .slot { border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Class Routine Generator</h1>
        
        <div class="controls">
            <div class="control-group">
                <h3>General Settings</h3>
                <label for="startTime">Start Time (e.g., 09:00)</label>
                <input type="time" id="startTime" value="09:00">
                
                <label for="periodDuration">Period Duration (minutes)</label>
                <input type="number" id="periodDuration" value="40">
                
                <label for="tiffinAfter">Tiffin Break After Period</label>
                <input type="number" id="tiffinAfter" value="4">
                
                <label for="tiffinDuration">Tiffin Duration (minutes)</label>
                <input type="number" id="tiffinDuration" value="30">
            </div>
            
            <div class="control-group">
                <h3>Working Days</h3>
                <label for="workingDays">Days per Week</label>
                <select id="workingDays">
                    <option value="5">5 (Mon-Fri)</option>
                    <option value="6" selected>6 (Mon-Sat)</option>
                </select>
                <small>Routine will be generated for Mon, Tue, Wed, Thu, Fri, Sat.</small>
            </div>

            <div class="control-group">
                <h3>Teacher Data (JSON)</h3>
                <label for="teacherData">Add/Edit Teachers</label>
                <textarea id="teacherData" rows="8"></textarea>
            </div>
            
            <div class="control-group">
                <h3>Class Data (JSON)</h3>
                <label for="classData">Add/Edit Classes & Subjects</label>
                <textarea id="classData" rows="8"></textarea>
            </div>
        </div>

        <button id="generate-btn">Generate Routine</button>

        <div id="routine-output" class="output-section">
            <h2>Generated Class Routine</h2>
            <div class="table-wrapper">
                <table class="routine-grid" id="routine-table">
                    </table>
            </div>
        </div>

        <div id="stats-output" class="output-section">
            <h2>Teacher Workload & Stats</h2>
            <table class="stats-grid" id="stats-table">
                </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        
        // === 1. DEFAULT DATA (MODEL) ===
        // This holds all our default "demo" data.
        
        const defaultSettings = {
            startTime: '09:00',
            periodDuration: 40,
            tiffinAfter: 4,
            tiffinDuration: 30,
            workingDays: 6, // 6 = Mon-Sat
            dayNames: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"]
        };

        // Default subjects for quick selection
        const subjects = {
            // General
            GEN_BAN: "Bangla",
            GEN_ENG: "English",
            GEN_MATH: "Mathematics",
            GEN_REL: "Religion",
            GEN_ICT: "ICT",
            GEN_GS: "General Science",
            GEN_BGS: "BGS", // Bangladesh & Global Studies
            GEN_PE: "Physical Ed.",
            GEN_ART: "Art",

            // Science
            SCI_PHY: "Physics",
            SCI_CHE: "Chemistry",
            SCI_BIO: "Biology",
            SCI_HMATH: "Higher Math",

            // Humanities
            HUM_HIS: "History",
            HUM_GEO: "Geography",
            HUM_CIV: "Civics",
            HUM_ECO: "Economics",

            // Commerce
            COM_ACC: "Accounting",
            COM_FIN: "Finance",
            COM_BM: "Business Mgmt."
        };

        // Default demo pool of 15+ teachers
        const defaultTeachers = [
            { id: "T01", name: "Mr. Alam", subjects: [subjects.GEN_BAN, subjects.HUM_HIS], maxPeriods: 25 },
            { id: "T02", name: "Ms. Fahmida", subjects: [subjects.GEN_ENG], maxPeriods: 24 },
            { id: "T03", name: "Mr. Kabir", subjects: [subjects.GEN_MATH, subjects.SCI_HMATH], maxPeriods: 25 },
            { id: "T04", name: "Mr. David", subjects: [subjects.SCI_PHY, subjects.GEN_GS], maxPeriods: 24 },
            { id: "T05", name: "Ms. Sharmin", subjects: [subjects.SCI_CHE, subjects.GEN_GS], maxPeriods: 23 },
            { id: "T06", name: "Mr. Barua", subjects: [subjects.SCI_BIO, subjects.GEN_GS], maxPeriods: 22 },
            { id: "T07", name: "Ms. Rosy", subjects: [subjects.GEN_BGS, subjects.HUM_GEO], maxPeriods: 25 },
            { id: "T08", name: "Mr. Paul", subjects: [subjects.COM_ACC, subjects.COM_FIN], maxPeriods: 24 },
            { id: "T09", name: "Ms. Tani", subjects: [subjects.HUM_CIV, subjects.HUM_ECO], maxPeriods: 22 },
            { id: "T10", name: "Mr. Ratan", subjects: [subjects.COM_BM], maxPeriods: 20 },
            { id: "T11", name: "Ms. Popy", subjects: [subjects.GEN_ICT, subjects.COM_BM], maxPeriods: 24 },
            { id: "T12", name: "Mr. Hamid", subjects: [subjects.GEN_REL], maxPeriods: 18 },
            { id: "T13", name: "Ms. Lipi", subjects: [subjects.GEN_ART, subjects.GEN_BAN], maxPeriods: 20 },
            { id: "T14", name: "Mr. Simon", subjects: [subjects.GEN_PE], maxPeriods: 18 },
            { id: "T15", name: "Ms. Jane", subjects: [subjects.GEN_ENG, subjects.HUM_HIS], maxPeriods: 25 },
            { id: "T16", name: "Mr. Razzaq", subjects: [subjects.GEN_MATH, subjects.SCI_PHY], maxPeriods: 24 },
        ];

        // Default classes (Nursery - 12) with departments and subject demands per WEEK
        const defaultClasses = [
            // School
            { id: "C00", name: "Nursery", periodsPerDay: 5, subjects: [
                { sub: subjects.GEN_BAN, periods: 6 },
                { sub: subjects.GEN_ENG, periods: 6 },
                { sub: subjects.GEN_MATH, periods: 5 },
                { sub: subjects.GEN_ART, periods: 7 },
                { sub: subjects.GEN_PE, periods: 6 }
            ]}, // Total: 30
            { id: "C01", name: "Class 1", periodsPerDay: 6, subjects: [
                { sub: subjects.GEN_BAN, periods: 7 },
                { sub: subjects.GEN_ENG, periods: 7 },
                { sub: subjects.GEN_MATH, periods: 6 },
                { sub: subjects.GEN_REL, periods: 4 },
                { sub: subjects.GEN_ART, periods: 6 },
                { sub: subjects.GEN_PE, periods: 6 }
            ]}, // Total: 36
            { id: "C06", name: "Class 6", periodsPerDay: 7, subjects: [
                { sub: subjects.GEN_BAN, periods: 7 },
                { sub: subjects.GEN_ENG, periods: 7 },
                { sub: subjects.GEN_MATH, periods: 6 },
                { sub: subjects.GEN_GS, periods: 5 },
                { sub: subjects.GEN_BGS, periods: 5 },
                { sub: subjects.GEN_REL, periods: 4 },
                { sub: subjects.GEN_ICT, periods: 3 },
                { sub: subjects.GEN_PE, periods: 2 },
                { sub: subjects.GEN_ART, periods: 3 }
            ]}, // Total: 42
            { id: "C08", name: "Class 8", periodsPerDay: 7, subjects: [
                { sub: subjects.GEN_BAN, periods: 7 },
                { sub: subjects.GEN_ENG, periods: 7 },
                { sub: subjects.GEN_MATH, periods: 6 },
                { sub: subjects.GEN_GS, periods: 5 },
                { sub: subjects.GEN_BGS, periods: 5 },
                { sub: subjects.GEN_REL, periods: 4 },
                { sub: subjects.GEN_ICT, periods: 3 },
                { sub: subjects.GEN_PE, periods: 2 },
                { sub: subjects.GEN_ART, periods: 3 }
            ]}, // Total: 42
            
            // Depts 9-10
            { id: "C09S", name: "Class 9 (Sci)", dept: "Science", periodsPerDay: 8, subjects: [
                { sub: subjects.GEN_BAN, periods: 6 },
                { sub: subjects.GEN_ENG, periods: 6 },
                { sub: subjects.GEN_MATH, periods: 5 },
                { sub: subjects.GEN_ICT, periods: 3 },
                { sub: subjects.GEN_REL, periods: 3 },
                { sub: subjects.GEN_BGS, periods: 4 },
                { sub: subjects.SCI_PHY, periods: 5 },
                { sub: subjects.SCI_CHE, periods: 5 },
                { sub: subjects.SCI_BIO, periods: 4 },
                { sub: subjects.SCI_HMATH, periods: 4 },
                { sub: subjects.GEN_PE, periods: 3 }
            ]}, // Total: 48
            { id: "C09H", name: "Class 9 (Hum)", dept: "Humanities", periodsPerDay: 8, subjects: [
                { sub: subjects.GEN_BAN, periods: 6 },
                { sub: subjects.GEN_ENG, periods: 6 },
                { sub: subjects.GEN_MATH, periods: 5 },
                { sub: subjects.GEN_ICT, periods: 3 },
                { sub: subjects.GEN_REL, periods: 3 },
                { sub: subjects.GEN_BGS, periods: 4 },
                { sub: subjects.HUM_HIS, periods: 6 },
                { sub: subjects.HUM_GEO, periods: 6 },
                { sub: subjects.HUM_CIV, periods: 6 },
                { sub: subjects.GEN_PE, periods: 3 }
            ]}, // Total: 48
            { id: "C09C", name: "Class 9 (Com)", dept: "Commerce", periodsPerDay: 8, subjects: [
                { sub: subjects.GEN_BAN, periods: 6 },
                { sub: subjects.GEN_ENG, periods: 6 },
                { sub: subjects.GEN_MATH, periods: 5 },
                { sub: subjects.GEN_ICT, periods: 3 },
                { sub: subjects.GEN_REL, periods: 3 },
                { sub: subjects.GEN_BGS, periods: 4 },
                { sub: subjects.COM_ACC, periods: 6 },
                { sub: subjects.COM_FIN, periods: 6 },
                { sub: subjects.COM_BM, periods: 6 },
                { sub: subjects.GEN_PE, periods: 3 }
            ]}, // Total: 48

            // College 11-12
            { id: "C11S", name: "Class 11 (Sci)", dept: "Science", periodsPerDay: 8, subjects: [
                { sub: subjects.GEN_BAN, periods: 5 },
                { sub: subjects.GEN_ENG, periods: 5 },
                { sub: subjects.GEN_ICT, periods: 4 },
                { sub: subjects.SCI_PHY, periods: 8 },
                { sub: subjects.SCI_CHE, periods: 8 },
                { sub: subjects.SCI_BIO, periods: 7 },
                { sub: subjects.SCI_HMATH, periods: 7 },
                { sub: subjects.GEN_PE, periods: 4 }
            ]}, // Total: 48
            { id: "C11C", name: "Class 11 (Com)", dept: "Commerce", periodsPerDay: 8, subjects: [
                { sub: subjects.GEN_BAN, periods: 5 },
                { sub: subjects.GEN_ENG, periods: 5 },
                { sub: subjects.GEN_ICT, periods: 4 },
                { sub: subjects.COM_ACC, periods: 8 },
                { sub: subjects.COM_FIN, periods: 8 },
                { sub: subjects.COM_BM, periods: 8 },
                { sub: subjects.HUM_ECO, periods: 6 },
                { sub: subjects.GEN_PE, periods: 4 }
            ]}, // Total: 48
        ];
        
        // --- End of Default Data ---

        // === 2. UI ELEMENT REFERENCES ===
        const btn = document.getElementById('generate-btn');
        const teacherDataEl = document.getElementById('teacherData');
        const classDataEl = document.getElementById('classData');
        const routineOutputEl = document.getElementById('routine-output');
        const routineTableEl = document.getElementById('routine-table');
        const statsOutputEl = document.getElementById('stats-output');
        const statsTableEl = document.getElementById('stats-table');

        // Settings inputs
        const startTimeEl = document.getElementById('startTime');
        const periodDurationEl = document.getElementById('periodDuration');
        const tiffinAfterEl = document.getElementById('tiffinAfter');
        const tiffinDurationEl = document.getElementById('tiffinDuration');
        const workingDaysEl = document.getElementById('workingDays');

        // === 3. HELPER FUNCTIONS ===
        
        /**
         * Loads default data into the textareas and settings fields.
         */
        function loadDefaults() {
            // Load settings
            startTimeEl.value = defaultSettings.startTime;
            periodDurationEl.value = defaultSettings.periodDuration;
            tiffinAfterEl.value = defaultSettings.tiffinAfter;
            tiffinDurationEl.value = defaultSettings.tiffinDuration;
            workingDaysEl.value = defaultSettings.workingDays;

            // Load JSON data into textareas (pretty-printed)
            teacherDataEl.value = JSON.stringify(defaultTeachers, null, 2);
            classDataEl.value = JSON.stringify(defaultClasses, null, 2);
        }

        /**
         * Parses all inputs from the UI.
         * @returns {object} { settings, teachers, classes }
         */
        function parseInputs() {
            try {
                const settings = {
                    startTime: startTimeEl.value,
                    periodDuration: parseInt(periodDurationEl.value),
                    tiffinAfter: parseInt(tiffinAfterEl.value),
                    tiffinDuration: parseInt(tiffinDurationEl.value),
                    workingDays: parseInt(workingDaysEl.value),
                    dayNames: defaultSettings.dayNames
                };
                
                const teachers = JSON.parse(teacherDataEl.value);
                const classes = JSON.parse(classDataEl.value);
                
                // --- VALIDATION ---
                if (!teachers.length || !classes.length) {
                    throw new Error("Teacher or Class data is empty.");
                }
                
                let validationErrors = [];
                classes.forEach(c => {
                    const totalPeriods = c.periodsPerDay * settings.workingDays;
                    const subjectPeriods = c.subjects.reduce((sum, s) => sum + s.periods, 0);
                    if (totalPeriods !== subjectPeriods) {
                        validationErrors.push(`Error in ${c.name}: Total slots (${totalPeriods}) do not match required subject periods (${subjectPeriods}).`);
                    }
                });
                
                if (validationErrors.length > 0) {
                    alert("Input Validation Error:\n\n" + validationErrors.join("\n"));
                    return null;
                }

                return { settings, teachers, classes };
                
            } catch (e) {
                alert("Error parsing JSON data. Please check your syntax.\n" + e.message);
                return null;
            }
        }

        /**
         * Calculates period start/end times.
         * @returns {string[]} Array of period time strings (e.g., "09:00 - 09:40")
         */
        function getPeriodTimes(settings) {
            let times = [];
            let [startHour, startMin] = settings.startTime.split(':').map(Number);
            
            let currentTime = new Date();
            currentTime.setHours(startHour, startMin, 0, 0);

            const maxPeriods = Math.max(...JSON.parse(classDataEl.value).map(c => c.periodsPerDay));

            for (let i = 1; i <= maxPeriods; i++) {
                if (i === settings.tiffinAfter + 1) {
                    // This is the TIFFIN period
                    let tiffinStart = new Date(currentTime.getTime());
                    currentTime.setMinutes(currentTime.getMinutes() + settings.tiffinDuration);
                    let tiffinEnd = new Date(currentTime.getTime());
                    times.push(`TIFFIN (${formatTime(tiffinStart)} - ${formatTime(tiffinEnd)})`);
                }

                let periodStart = new Date(currentTime.getTime());
                currentTime.setMinutes(currentTime.getMinutes() + settings.periodDuration);
                let periodEnd = new Date(currentTime.getTime());
                times.push(`${formatTime(periodStart)} - ${formatTime(periodEnd)}`);
            }
            return times;
        }

        /**
         * Formats a Date object to HH:MM string.
         */
        function formatTime(date) {
            let h = date.getHours().toString().padStart(2, '0');
            let m = date.getMinutes().toString().padStart(2, '0');
            return `${h}:${m}`;
        }

        /**
         * Gets a "dept" class for color-coding.
         */
        function getDeptClass(dept) {
            if (dept === "Science") return "slot-sci";
            if (dept === "Humanities") return "slot-hum";
            if (dept === "Commerce") return "slot-com";
            return "slot-gen";
        }

        // === 4. THE GENERATION ALGORITHM ===
        
        btn.addEventListener('click', () => {
            const inputData = parseInputs();
            if (!inputData) return;

            const { settings, teachers, classes } = inputData;
            
            // --- 1. Initialization ---
            let routineGrid = {}; // { classId: { day: { period: slot } } }
            let teacherSchedules = {}; // { teacherId: { day: { period: busy? } } }
            let teacherWorkload = {}; // { teacherId: 0 }
            let classSubjectNeeds = {}; // { classId: { subjectName: count } }
            let stats = {
                clashes: 0,
                fallbacks: 0,
                totalAssignments: 0
            };

            const maxPeriods = Math.max(...classes.map(c => c.periodsPerDay));
            
            // Init data structures
            teachers.forEach(t => {
                teacherWorkload[t.id] = 0;
                teacherSchedules[t.id] = {};
                for (let d = 0; d < settings.workingDays; d++) {
                    teacherSchedules[t.id][d] = {};
                }
            });

            classes.forEach(c => {
                routineGrid[c.id] = {};
                classSubjectNeeds[c.id] = {};
                c.subjects.forEach(s => {
                    classSubjectNeeds[c.id][s.sub] = s.periods;
                });
                
                for (let d = 0; d < settings.workingDays; d++) {
                    routineGrid[c.id][d] = {};
                    for (let p = 1; p <= maxPeriods; p++) {
                        if (p > c.periodsPerDay) {
                            // This class doesn't have this period
                            routineGrid[c.id][d][p] = { type: 'empty' };
                        } else if (p === settings.tiffinAfter) {
                            // Tiffin break
                            routineGrid[c.id][d][p] = { type: 'tiffin' };
                        } else {
                            // Needs assignment
                            routineGrid[c.id][d][p] = { type: 'unassigned' };
                        }
                    }
                }
            });

            // --- 2. The Main Loop (Greedy Algorithm) ---
            // Iterate Day -> Period -> Class
            for (let d = 0; d < settings.workingDays; d++) {
                for (let p = 1; p <= maxPeriods; p++) {
                    if (p === settings.tiffinAfter) continue; // Skip tiffin

                    // Shuffle classes for fairness (prevents C01 always getting the best teachers)
                    let shuffledClasses = [...classes].sort(() => Math.random() - 0.5);

                    for (const c of shuffledClasses) {
                        // Skip if class doesn't have this period or is already assigned
                        if (routineGrid[c.id][d][p].type !== 'unassigned') continue;
                        
                        const assignment = findBestAssignment(c, d, p);
                        
                        if (assignment) {
                            // **Assign the slot**
                            routineGrid[c.id][d][p] = {
                                type: 'class',
                                subject: assignment.subject,
                                teacher: assignment.teacher,
                                isFallback: assignment.isFallback,
                                dept: c.dept
                            };
                            
                            // **Update state**
                            teacherSchedules[assignment.teacher.id][d][p] = true;
                            teacherWorkload[assignment.teacher.id]++;
                            if (assignment.isFallback) {
                                stats.fallbacks++;
                            } else {
                                classSubjectNeeds[c.id][assignment.subject]--;
                            }
                            stats.totalAssignments++;
                        } else {
                            // This should not happen if fallback logic is correct
                            // But as a safety, mark it as unassigned
                            routineGrid[c.id][d][p] = { type: 'unassigned', subject: 'ERROR' };
                        }
                    }
                }
            }

            // --- 3. Helper Function: `findBestAssignment` ---
            function findBestAssignment(c, d, p) {
                // Get all teachers sorted by workload (ascending)
                const availableTeachers = teachers
                    .filter(t => !teacherSchedules[t.id][d][p]) // Not busy
                    .filter(t => teacherWorkload[t.id] < (t.maxPeriods || 30)) // Not overworked
                    .sort((a, b) => teacherWorkload[a.id] - teacherWorkload[b.id]);

                if (availableTeachers.length === 0) {
                    // **CRITICAL: No teachers free.** This is a resource clash.
                    // This can happen if all teachers are busy.
                    stats.clashes++;
                    return { 
                        subject: "CLASH!", 
                        teacher: { id: 'T_ERR', name: 'No Teacher Free' }, 
                        isFallback: true 
                    };
                }

                // **Priority 1: Find a Specialist**
                // Get subjects this class still needs
                const neededSubjects = c.subjects
                    .filter(s => classSubjectNeeds[c.id][s.sub] > 0)
                    .map(s => s.sub);

                for (const subject of neededSubjects) {
                    // Find the best teacher for THIS subject
                    const specialist = availableTeachers.find(t => t.subjects.includes(subject));
                    if (specialist) {
                        return {
                            subject: subject,
                            teacher: specialist,
                            isFallback: false
                        };
                    }
                }

                // **Priority 2: Fallback (Implement "No Empty Period" Rule)**
                // No specialist free, or all subject demands are met (unlikely)
                // Assign the *first available teacher* (who has the lowest workload)
                // This ensures the class is supervised.
                return {
                    subject: "General Study", // A generic fallback subject
                    teacher: availableTeachers[0],
                    isFallback: true
                };
            }

            // --- 4. Render Results ---
            renderRoutine(routineGrid, classes, settings);
            renderStats(teacherWorkload, teachers, stats, classSubjectNeeds, classes);
            
            routineOutputEl.style.display = 'block';
            statsOutputEl.style.display = 'block';
        });

        // === 5. RENDER FUNCTIONS ===

        /**
         * Renders the main routine table.
         */
        function renderRoutine(routineGrid, classes, settings) {
            routineTableEl.innerHTML = ''; // Clear old table
            
            const periodTimes = getPeriodTimes(settings);
            const maxPeriods = Math.max(...classes.map(c => c.periodsPerDay));
            
            // 1. Create Header Row
            let a = settings.tiffinAfter;
            let header = `<tr><th>Period</th>`;
            for(let p = 1; p <= maxPeriods; p++) {
                let timeIndex = (p <= a) ? (p-1) : (p); // Adjust index for tiffin
                header += `<th>Period ${p}<br><small>${periodTimes[timeIndex-1]}</small></th>`;
                if(p === a) {
                    header += `<th><small>${periodTimes[a-1]}</small></th>`;
                }
            }
            header += `</tr>`;
            header += `<tr><th>Class</th>` + 
                      `<th colspan="${maxPeriods + (a <= maxPeriods ? 1: 0)}">Monday - Saturday (Full Week)</th>` + 
                      `</tr>`; // Placeholder, real data is col-by-col
            // Not adding header to keep it clean, but this is an idea
            
            // 2. Create Data Rows
            let body = '';
            for (const c of classes) {
                body += `<tr><td class="class-name"><b>${c.name}</b></td>`;
                
                let dayRow = ''; // Build the full week row
                for (let d = 0; d < settings.workingDays; d++) {
                    for (let p = 1; p <= maxPeriods; p++) {
                        if (p > c.periodsPerDay) continue; // Skip periods this class doesn't have

                        const slot = routineGrid[c.id][d][p];
                        let cellContent = '';
                        
                        if (slot.type === 'tiffin') {
                            cellContent = `<div class="slot tiffin">TIFFIN</div>`;
                        } else if (slot.type === 'class') {
                            const deptClass = getDeptClass(c.dept);
                            const fallbackClass = slot.isFallback ? 'fallback' : '';
                            cellContent = `<div class="slot ${deptClass} ${fallbackClass}">
                                <strong>${slot.subject}</strong>
                                <small>${slot.teacher.name} (${settings.dayNames[d]})</small>
                            </div>`;
                        } else if (slot.type === 'unassigned') {
                             cellContent = `<div class="slot fallback">ERROR</div>`;
                        }
                        // 'empty' type produces no cell
                        
                        if(cellContent) {
                            dayRow += `<td>${cellContent}</td>`;
                        }
                    }
                }
                
                // This layout is non-standard. Let's fix it.
                // The user wants a standard grid: Class vs. Day/Period
            }
            
            // --- New Render Logic (Standard Grid) ---
            routineTableEl.innerHTML = ''; // Clear again
            
            // Create Header
            let thead = '<thead><tr><th>Class</th>';
            for (let d = 0; d < settings.workingDays; d++) {
                let day = settings.dayNames[d];
                let pCount = Math.max(...classes.map(c => c.periodsPerDay));
                thead += `<th colspan="${pCount}">${day}</th>`;
            }
            thead += '</tr>';

            thead += '<tr><th>(Periods)</th>';
             for (let d = 0; d < settings.workingDays; d++) {
                let pCount = Math.max(...classes.map(c => c.periodsPerDay));
                for(let p = 1; p <= pCount; p++) {
                    thead += `<th>P${p}</th>`;
                }
            }
            thead += '</tr></thead>';
            routineTableEl.insertAdjacentHTML('beforeend', thead);
            
            // Create Body
            let tbody = '<tbody>';
            for (const c of classes) {
                tbody += `<tr><td><b>${c.name}</b></td>`;
                for (let d = 0; d < settings.workingDays; d++) {
                    let pCount = Math.max(...classes.map(c => c.periodsPerDay));
                    for(let p = 1; p <= pCount; p++) {
                        
                        let cellContent = '';
                        if (p > c.periodsPerDay) {
                            cellContent = '<div class="slot empty"></div>'; // No class
                        } else {
                            const slot = routineGrid[c.id][d][p];
                            if (slot.type === 'tiffin') {
                                cellContent = `<div class="slot tiffin">Tiffin</div>`;
                            } else if (slot.type === 'class') {
                                const deptClass = getDeptClass(c.dept);
                                const fallbackClass = slot.isFallback ? 'fallback' : '';
                                cellContent = `<div class="slot ${deptClass} ${fallbackClass}">
                                    <strong>${slot.subject}</strong>
                                    <small>${slot.teacher.name}</small>
                                </div>`;
                            } else {
                                cellContent = `<div class="slot fallback">ERR</div>`;
                            }
                        }
                        tbody += `<td>${cellContent}</td>`;
                    }
                }
                tbody += '</tr>';
            }
            tbody += '</tbody>';
            routineTableEl.insertAdjacentHTML('beforeend', tbody);
        }

        /**
         * Renders the stats and workload table.
         */
        function renderStats(teacherWorkload, teachers, stats, classSubjectNeeds, classes) {
            statsTableEl.innerHTML = ''; // Clear old stats
            
            // 1. Teacher Workload
            let header = `<thead><tr>
                <th>Teacher</th>
                <th>Subjects</th>
                <th>Workload (Assigned / Max)</th>
                <th>%</th>
            </tr></thead>`;
            let body = '<tbody>';
            
            for (const t of teachers) {
                const assigned = teacherWorkload[t.id] || 0;
                const max = t.maxPeriods || 30;
                const percent = (assigned / max) * 100;
                const overloadClass = percent > 100 ? 'overload' : '';
                
                body += `<tr>
                    <td>${t.name}</td>
                    <td><small>${t.subjects.join(', ')}</small></td>
                    <td>${assigned} / ${max}</td>
                    <td>
                        <div class="stats-bar">
                            <div class="stats-bar-inner ${overloadClass}" style="width: ${Math.min(percent, 100)}%;"></div>
                        </div>
                    </td>
                </tr>`;
            }
            body += '</tbody>';
            statsTableEl.innerHTML = header + body;
            
            // 2. Class Subject Fulfillment
            let classStatsHeader = `<thead><tr>
                <th>Class</th>
                <th>Subject</th>
                <th>Required</th>
                <th>Unmet</th>
            </tr></thead>`;
            let classStatsBody = '<tbody>';
            
            for (const c of classes) {
                let first = true;
                for (const subjectName in classSubjectNeeds[c.id]) {
                    const unmet = classSubjectNeeds[c.id][subjectName];
                    const required = c.subjects.find(s => s.sub === subjectName).periods;
                    const errorClass = unmet > 0 ? 'fallback' : '';
                    
                    classStatsBody += `<tr class="${errorClass}">
                        <td>${first ? c.name : ''}</td>
                        <td>${subjectName}</td>
                        <td>${required}</td>
                        <td>${unmet}</td>
                    </tr>`;
                    first = false;
                }
            }
            classStatsBody += '</tbody>';
            // Prepend this to the stats table
            statsTableEl.innerHTML = classStatsHeader + classStatsBody + header + body;

            // 3. General Stats
            let generalStats = `<p>
                <b>Total Assignments:</b> ${stats.totalAssignments} | 
                <b>Fallback Periods:</b> ${stats.fallbacks} | 
                <b>Teacher Clashes:</b> ${stats.clashes}
            </p>`;
            statsTableEl.insertAdjacentHTML('beforebegin', generalStats);
        }

        // --- Initial Load ---
        loadDefaults();
    });
    </script>

</body>
</html>