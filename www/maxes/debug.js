// debug.js
// =====================
// Switch JS paths in imports.xml between production and debug mode
// Usage: node debug.js -assets -home -workorder
// =====================

const fs = require("fs");
const path = require("path");

const XML_FILE = "applications/properties/imports.xml";

// Parse command line arguments
const args = process.argv.slice(2);

if (args.length === 0) {
    console.log("\n❌ No module specified!");
    console.log("\n📖 Usage:");
    console.log("   node debug.js -assets");
    console.log("   node debug.js -home -workorder");
    console.log("   node debug.js -all  (switch all modules)\n");
    process.exit(1);
}

// Read XML file
if (!fs.existsSync(XML_FILE)) {
    console.error(`❌ File not found: ${XML_FILE}`);
    process.exit(1);
}

let xmlContent = fs.readFileSync(XML_FILE, "utf8");
let changesCount = 0;

console.log("\n╔════════════════════════════════════════╗");
console.log("║  🔧 DEBUG MODE SWITCHER                ║");
console.log("╚════════════════════════════════════════╝\n");

// Process -all flag
if (args.includes("-all")) {
    console.log("🌐 Processing ALL modules...\n");
    
    // Check current state
    const hasProduction = xmlContent.includes("applications/ui/") && 
                          !xmlContent.includes("applications/ui-src/");
    const hasDebug = xmlContent.includes("applications/ui-src/");
    
    if (hasProduction || (!hasProduction && !hasDebug)) {
        // Switch to debug: ui → ui-src, .min.js → .js
        console.log("🔄 Mode: PRODUCTION → DEBUG\n");
        xmlContent = xmlContent.replace(
            /applications\/ui\//g,
            "applications/ui-src/"
        );
        xmlContent = xmlContent.replace(
            /\.min\.js/g,
            ".js"
        );
    } else if (hasDebug) {
        // Switch to production: ui-src → ui, .js → .min.js
        console.log("🔄 Mode: DEBUG → PRODUCTION\n");
        xmlContent = xmlContent.replace(
            /applications\/ui-src\//g,
            "applications/ui/"
        );
        // Replace .js with .min.js (but not .json)
        xmlContent = xmlContent.replace(
            /(\w+)\.js(?!on)/g,
            "$1.min.js"
        );
    }
    
    changesCount = (xmlContent.match(/applications\/ui/g) || []).length;
    
} else {
    // Process specific modules
    args.forEach(moduleArg => {
        const moduleName = moduleArg.replace(/^-/, "");
        
        console.log(`🔍 Searching for module: ${moduleName}`);
        
        // Find the path block for this module
        const pathRegex = new RegExp(
            `<path id="${moduleName}">[\\s\\S]*?</path>`,
            "g"
        );
        
        const matches = xmlContent.match(pathRegex);
        
        if (!matches) {
            console.log(`   ⚠️  Module "${moduleName}" not found in XML\n`);
            return;
        }
        
        matches.forEach(match => {
            let newBlock = match;
            
            // Check current state of THIS specific block
            const blockIsProduction = newBlock.includes("applications/ui/") && 
                                       !newBlock.includes("applications/ui-src/");
            const blockIsDebug = newBlock.includes("applications/ui-src/");
            
            if (blockIsProduction) {
                // Switch to debug mode
                newBlock = newBlock.replace(
                    /applications\/ui\//g,
                    "applications/ui-src/"
                );
                newBlock = newBlock.replace(
                    /\.min\.js/g,
                    ".js"
                );
                
                xmlContent = xmlContent.replace(match, newBlock);
                changesCount++;
                console.log(`   ✔ Switched to DEBUG mode (ui-src + .js)\n`);
                
            } else if (blockIsDebug) {
                // Switch to production mode
                newBlock = newBlock.replace(
                    /applications\/ui-src\//g,
                    "applications/ui/"
                );
                // Replace .js with .min.js (but not if already .min.js)
                newBlock = newBlock.replace(
                    /(\w+)\.js(?!on)/g,
                    "$1.min.js"
                );
                
                xmlContent = xmlContent.replace(match, newBlock);
                changesCount++;
                console.log(`   ✔ Switched to PRODUCTION mode (ui + .min.js)\n`);
                
            } else {
                console.log(`   ℹ️  No script path found for this module\n`);
            }
        });
    });
}

// Write back to file
if (changesCount > 0) {
    fs.writeFileSync(XML_FILE, xmlContent);
    
    console.log("════════════════════════════════════════");
    console.log(`✅ Changes applied: ${changesCount} module(s)`);
    console.log(`📝 File updated: ${XML_FILE}`);
    console.log("════════════════════════════════════════\n");
} else {
    console.log("════════════════════════════════════════");
    console.log("ℹ️  No changes needed");
    console.log("════════════════════════════════════════\n");
}