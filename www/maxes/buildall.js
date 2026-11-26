// buildall.js
// =====================
// BATCH MINIFY + OBFUSCATE ALL JS FILES
// Support: -minify, default (LOW), -medium, -strong
// =====================

const fs = require("fs");
const path = require("path");
const terser = require("terser");
const obfuscator = require("javascript-obfuscator");

// ROOT FOLDER CONFIG
const SRC = "applications/ui-src";
const DEST = "applications/ui";

// Parse command line arguments
const args = process.argv.slice(2);
const mode = args[0] || "default"; // default = LOW obfuscation

// Obfuscation presets
const OBFUSCATION_PRESETS = {
    minify: null, // No obfuscation, minify only
    low: {
        compact: true,
        controlFlowFlattening: false,
        deadCodeInjection: false,
        debugProtection: false,
        debugProtectionInterval: 0,
        disableConsoleOutput: false,
        identifierNamesGenerator: "hexadecimal",
        log: false,
        numbersToExpressions: false,
        renameGlobals: false,
        selfDefending: false,
        simplify: true,
        splitStrings: false,
        stringArray: false,
        stringArrayThreshold: 0,
        transformObjectKeys: false,
        unicodeEscapeSequence: false
    },
    medium: {
        compact: true,
        controlFlowFlattening: true,
        controlFlowFlatteningThreshold: 0.5,
        deadCodeInjection: true,
        deadCodeInjectionThreshold: 0.2,
        debugProtection: false,
        disableConsoleOutput: true,
        identifierNamesGenerator: "hexadecimal",
        log: false,
        numbersToExpressions: true,
        renameGlobals: false,
        selfDefending: true,
        simplify: true,
        splitStrings: true,
        splitStringsChunkLength: 5,
        stringArray: true,
        stringArrayThreshold: 0.5,
        transformObjectKeys: true,
        unicodeEscapeSequence: false
    },
    strong: {
        compact: true,
        controlFlowFlattening: true,
        controlFlowFlatteningThreshold: 1,
        deadCodeInjection: true,
        deadCodeInjectionThreshold: 0.4,
        debugProtection: true,
        debugProtectionInterval: 4000,
        disableConsoleOutput: true,
        identifierNamesGenerator: "mangled",
        log: false,
        numbersToExpressions: true,
        renameGlobals: true,
        selfDefending: true,
        simplify: true,
        splitStrings: true,
        splitStringsChunkLength: 3,
        stringArray: true,
        stringArrayCallsTransform: true,
        stringArrayEncoding: ["rc4"],
        stringArrayIndexShift: true,
        stringArrayRotate: true,
        stringArrayShuffle: true,
        stringArrayWrappersCount: 5,
        stringArrayWrappersChainedCalls: true,
        stringArrayWrappersParametersMaxCount: 5,
        stringArrayWrappersType: "function",
        stringArrayThreshold: 1,
        transformObjectKeys: true,
        unicodeEscapeSequence: true
    }
};

// Determine obfuscation level
let obfuscationLevel;
let isMinifyOnly = false;

switch (mode) {
    case "-minify":
        isMinifyOnly = true;
        obfuscationLevel = "MINIFY ONLY";
        break;
    case "-medium":
        obfuscationLevel = "MEDIUM";
        break;
    case "-strong":
        obfuscationLevel = "STRONG";
        break;
    default:
        obfuscationLevel = "LOW";
}

console.log("\n╔════════════════════════════════════════╗");
console.log(`║  🚀 BUILD ALL MODE: ${obfuscationLevel.padEnd(19)}║`);
console.log("╚════════════════════════════════════════╝\n");

// Utility: Ensures directory exists
function ensureDir(dir) {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
}

// Utility: Get all .js files recursively
function getAllJsFiles(dir, fileList = []) {
    const files = fs.readdirSync(dir);

    files.forEach(file => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);

        if (stat.isDirectory()) {
            getAllJsFiles(filePath, fileList);
        } else if (file.endsWith(".js") && !file.endsWith(".min.js")) {
            fileList.push(filePath);
        }
    });

    return fileList;
}

// PROCESS SINGLE FILE
async function processFile(filePath, index, total) {
    try {
        const relative = path.relative(SRC, filePath);

        // output file otomatis mirror folder
        const outFile = path.join(
            DEST,
            relative.replace(".js", ".min.js")
        );

        ensureDir(path.dirname(outFile));

        const code = fs.readFileSync(filePath, "utf8");

        // STEP 1: MINIFY
        const minified = await terser.minify(code, {
            compress: {
                dead_code: true,
                drop_console: !isMinifyOnly,
                drop_debugger: true,
                keep_classnames: false,
                keep_fnames: false
            },
            mangle: true
        });
        
        if (!minified.code) throw new Error("Terser returned empty output");

        let finalCode = minified.code;

        // STEP 2: OBFUSCATE (if not minify-only mode)
        if (!isMinifyOnly) {
            const presetKey = obfuscationLevel.toLowerCase();
            const obfOptions = OBFUSCATION_PRESETS[presetKey];
            
            const obf = obfuscator.obfuscate(minified.code, obfOptions);
            finalCode = obf.getObfuscatedCode();
        }

        fs.writeFileSync(outFile, finalCode);

        console.log(`[${index}/${total}] ✔ ${relative} → ${path.relative(process.cwd(), outFile)}`);

        return { success: true, file: relative };

    } catch (err) {
        console.error(`[${index}/${total}] ❌ ${path.relative(SRC, filePath)} - ERROR: ${err.message}`);
        return { success: false, file: path.relative(SRC, filePath), error: err.message };
    }
}

// MAIN FUNCTION
async function buildAll() {
    console.log(`📂 Source: ${SRC}`);
    console.log(`📂 Destination: ${DEST}\n`);

    // Check if source directory exists
    if (!fs.existsSync(SRC)) {
        console.error(`❌ Source directory not found: ${SRC}`);
        process.exit(1);
    }

    // Get all JS files
    console.log("🔍 Scanning for .js files...\n");
    const allFiles = getAllJsFiles(SRC);

    if (allFiles.length === 0) {
        console.log("⚠️  No .js files found in source directory");
        return;
    }

    console.log(`📝 Found ${allFiles.length} file(s) to process\n`);
    console.log("════════════════════════════════════════\n");

    const startTime = Date.now();
    const results = [];

    // Process all files
    for (let i = 0; i < allFiles.length; i++) {
        const result = await processFile(allFiles[i], i + 1, allFiles.length);
        results.push(result);
    }

    const endTime = Date.now();
    const duration = ((endTime - startTime) / 1000).toFixed(2);

    // Summary
    const successful = results.filter(r => r.success).length;
    const failed = results.filter(r => !r.success).length;

    console.log("\n════════════════════════════════════════");
    console.log("\n📊 BUILD SUMMARY");
    console.log("────────────────────────────────────────");
    console.log(`✔ Successful: ${successful}`);
    console.log(`❌ Failed: ${failed}`);
    console.log(`⏱️  Duration: ${duration}s`);
    console.log("════════════════════════════════════════\n");

    if (failed > 0) {
        console.log("❌ Failed files:");
        results.filter(r => !r.success).forEach(r => {
            console.log(`   - ${r.file}: ${r.error}`);
        });
        console.log();
    }
}

// Run the build
buildAll().catch(err => {
    console.error("\n❌ FATAL ERROR:", err);
    process.exit(1);