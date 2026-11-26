// buildjs.js
// =====================
// AUTO MINIFY + AUTO OBFUSCATE + MIRROR FOLDER
// Support: -minify, default (LOW), -medium, -strong
// =====================

const fs = require("fs");
const path = require("path");
const chokidar = require("chokidar");
const terser = require("terser");
const obfuscator = require("javascript-obfuscator");

// ROOT FOLDER CONFIG
const WATCH_FOLDERS = [
    { src: "applications/ui-src", dest: "applications/ui" },
    { src: "js-src", dest: "js" }
];

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

console.log(`\n🚀 Mode: ${obfuscationLevel}\n`);

// Utility: Ensures directory exists
function ensureDir(dir) {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
}

// PROCESS FILE
async function processFile(filePath, srcFolder, destFolder) {
    // abaikan file .min.js biar tidak recursive
    if (filePath.endsWith(".min.js")) return;

    // hanya proses file .js
    if (!filePath.endsWith(".js")) return;

    console.log("\n====================================");
    console.log("🔄  Processing:", filePath);

    try {
        const relative = path.relative(srcFolder, filePath);

        // output file otomatis mirror folder
        const outFile = path.join(
            destFolder,
            relative.replace(".js", ".min.js")
        );

        ensureDir(path.dirname(outFile));

        const code = fs.readFileSync(filePath, "utf8");

        // STEP 1: MINIFY
        console.log("  ➤ Minifying...");
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
            console.log(`  ➤ Obfuscating (${obfuscationLevel})...`);
            
            const presetKey = obfuscationLevel.toLowerCase();
            const obfOptions = OBFUSCATION_PRESETS[presetKey];
            
            const obf = obfuscator.obfuscate(minified.code, obfOptions);
            finalCode = obf.getObfuscatedCode();
        }

        fs.writeFileSync(outFile, finalCode);

        console.log("  ✔ Output:", outFile);
        console.log("====================================");

    } catch (err) {
        console.error("❌ ERROR:", err.message);
        console.error(err);
    }
}

// WATCHER - Setup for multiple folders
WATCH_FOLDERS.forEach(({ src, dest }) => {
    console.log(`👀 Watching: ${src} → ${dest}`);

    chokidar
        .watch(src, { ignoreInitial: true })
        .on("add", (file) => processFile(file, src, dest))
        .on("change", (file) => processFile(file, src, dest))
        .on("unlink", (file) => {
            // Automatically delete mirrored file
            const relative = path.relative(src, file);
            const outFile = path.join(
                dest,
                relative.replace(".js", ".min.js")
            );
            if (fs.existsSync(outFile)) {
                fs.unlinkSync(outFile);
                console.log("🗑  Deleted:", outFile);
            }
        });
});

console.log("\n✅ All watchers active!\n");