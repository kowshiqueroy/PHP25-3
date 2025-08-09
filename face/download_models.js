const https = require('https');
const fs = require('fs');
const path = require('path');

const models = [
    'tiny_face_detector_model-weights_manifest.json',
    'tiny_face_detector_model.weights',
    'face_landmark_68_model-weights_manifest.json',
    'face_landmark_68_model.weights',
    'face_recognition_model-weights_manifest.json',
    'face_recognition_model.weights',
    'face_expression_model-weights_manifest.json',
    'face_expression_model.weights',
    'age_gender_model-weights_manifest.json',
    'age_gender_model.weights',
    'ssd_mobilenetv1_model-weights_manifest.json',
    'ssd_mobilenetv1_model.weights'
];

const download = (url, dest) => {
    return new Promise((resolve, reject) => {
        const file = fs.createWriteStream(dest);
        https.get(url, (response) => {
            response.pipe(file);
            file.on('finish', () => {
                file.close(resolve);
            });
        }).on('error', (err) => {
            fs.unlink(dest);
            reject(err.message);
        });
    });
};

const modelPath = path.join(__dirname, 'assets', 'js', 'face-api-models');
if (!fs.existsSync(modelPath)) {
    fs.mkdirSync(modelPath, { recursive: true });
}

(async () => {
    for (const model of models) {
        const url = `https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/${model}`;
        const dest = path.join(modelPath, model);
        console.log(`Downloading ${model}...`);
        await download(url, dest);
        console.log(`Downloaded ${model} to ${dest}`);
    }
})();