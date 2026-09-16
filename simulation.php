<?php 
session_start(); 
include 'config.php'; 

if(!isset($_SESSION['uid'])){ 
    header("Location: index.php"); 
    exit(); 
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NC3 Electronics & Mobile Servicing 3D Lab</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<style>
:root {
    --bg-dark: #090d16;
    --panel-bg: rgba(15, 23, 42, 0.88);
    --panel-border: rgba(255, 255, 255, 0.12);
    --accent-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    --text-main: #f8fafc;
    --text-muted: #94a3b8;
}

*{margin:0;padding:0;box-sizing:border-box;user-select:none}
body{
    font-family:'Plus Jakarta Sans', sans-serif;
    background: var(--bg-dark);
    color: var(--text-main);
    overflow:hidden;
    height: 100vh;
}

.topbar{
    position:fixed;top:0;left:0;width:100%;
    background: rgba(9, 13, 22, 0.85);
    backdrop-filter: blur(16px);
    padding: 16px 32px; display:flex; justify-content:space-between; align-items:center;
    border-bottom: 1px solid var(--panel-border); z-index:100;
}
.logo{ display:flex; align-items:center; gap:12px; font-weight:800; font-size:18px; }
.logo-badge {
    background: var(--accent-gradient);
    padding: 4px 8px; border-radius: 6px; font-size: 11px;
    text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;
}
.btn-back{
    color: var(--text-main); text-decoration:none; font-weight:600; font-size:13px;
    background: rgba(255, 255, 255, 0.06); padding:10px 20px; border-radius:10px;
    border: 1px solid var(--panel-border); transition: all 0.2s ease;
}
.btn-back:hover{ background: rgba(255, 255, 255, 0.12); transform: translateY(-1px); }

.controls-panel{
    position:fixed; left:24px; top:88px; width:380px;
    background: var(--panel-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--panel-border); border-radius:20px; padding:20px; z-index:90;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}
.panel-title{ color: var(--text-muted); font-size:11px; font-weight: 700; text-transform:uppercase; margin-bottom: 12px; }
.mod-btn{
    width:100%; padding:12px 14px; margin-bottom:8px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--panel-border); border-radius: 12px;
    color: var(--text-muted); font-weight:600; font-size:13px;
    cursor:pointer; text-align:left; transition: all 0.25s ease;
    display: flex; align-items: center; justify-content: space-between;
}
.mod-btn:hover{ background: rgba(255, 255, 255, 0.07); color: #fff; }
.mod-btn.active{
    background: var(--accent-gradient);
    border-color: transparent; color:#fff;
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
}

.step-card { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--panel-border); }
.step-badge {
    display: inline-block; padding: 4px 10px; background: rgba(139, 92, 246, 0.15);
    border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 20px;
    font-size: 11px; color: #c084fc; font-weight: 700; margin-bottom: 8px;
}
.step-guide { font-size: 13px; color: #cbd5e1; line-height: 1.5; }

.key-legend {
    margin-top: 12px; background: rgba(0,0,0,0.3); padding: 10px;
    border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);
}
.key-row { display: flex; justify-space-between; font-size: 11px; margin-bottom: 4px; color: #94a3b8; }
.key-cap {
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
    border-radius: 4px; padding: 1px 5px; color: #fff; font-weight: 700;
}

.instruction-card{
    position:fixed; bottom:24px; left:50%; transform:translateX(-50%);
    background: var(--panel-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--panel-border); padding: 16px 32px; border-radius: 20px;
    z-index:90; text-align:center; box-shadow: 0 20px 50px rgba(0,0,0,0.6);
    display:flex; flex-direction:column; align-items:center; gap:10px;
    min-width: 480px;
}
.instruction-text { font-size: 14px; font-weight: 600; color: #f1f5f9; }
.heat-meter{
    width:100%; height:8px; background: rgba(0,0,0,0.4);
    border-radius:10px; overflow:hidden; border: 1px solid rgba(255,255,255,0.08);
}
.heat-fill{
    width:0%; height:100%;
    background: linear-gradient(90deg, #f59e0b, #ef4444);
    border-radius:10px; transition: width 0.1s linear;
}

#webgl-container{ width:100vw; height:100vh; position:absolute; top:0; left:0; z-index:1; }
</style>
</head>
<body>

<div class="topbar">
    <div class="logo">
        <span class="logo-badge">NC3 Servicing</span>
        Micro-USB Port Replacement & Mobile Servicing Lab
    </div>
    <a href="dashboard.php" class="btn-back">← Exit Simulation</a>
</div>

<div class="controls-panel">
    <div class="panel-title">Training Modules</div>
    
    <button class="mod-btn active" onclick="switchModule('solder')">
        <span>🔌 Micro-USB Port Replacement</span>
        <span style="font-size:10px;opacity:0.7">Mod 1</span>
    </button>
    <button class="mod-btn" onclick="switchModule('reformat')">
        <span>📱 Mobile Phone Reformatting</span>
        <span style="font-size:10px;opacity:0.7">Mod 2</span>
    </button>
    <button class="mod-btn" onclick="switchModule('disassemble')">
        <span>🔧 Gadget Disassembly & Assembly</span>
        <span style="font-size:10px;opacity:0.7">Mod 3</span>
    </button>

    <div class="step-card">
        <span class="step-badge" id="stepBadge">MODULE 1 / STEP 1</span>
        <p class="step-guide" id="stepGuide">
            Position <b>Hot Air Gun</b> over damaged port using <b>WASD Keys</b> or Mouse, then hold <span class="key-cap">SPACEBAR</span> to melt solder.
        </p>
    </div>

    <div class="key-legend" id="keyLegend">
        <div id="mod1Keys">
            <div class="key-row"><span>Move Tool (XY Axis):</span> <span><span class="key-cap">W</span><span class="key-cap">A</span><span class="key-cap">S</span><span class="key-cap">D</span> / Arrows</span></div>
            <div class="key-row"><span>Raise / Lower Tool:</span> <span><span class="key-cap">Q</span> / <span class="key-cap">E</span></span></div>
            <div class="key-row"><span>Apply Heat / Solder:</span> <span>Hold <span class="key-cap">SPACEBAR</span></span></div>
        </div>
        <div id="mod2Keys" style="display:none;">
            <div class="key-row"><span>Hold Power Button:</span> <span>Hold <span class="key-cap">P</span> Key</span></div>
            <div class="key-row"><span>Hold Volume Buttons:</span> <span>Hold <span class="key-cap">▲ UP</span> or <span class="key-cap">▼ DOWN</span> Key</span></div>
            <div class="key-row"><span>Recovery Boot Combo:</span> <span>Hold <span class="key-cap">P</span> + <span class="key-cap">▲ UP</span> (or <span class="key-cap">▼ DOWN</span>)</span></div>
        </div>
        <div id="mod3Keys" style="display:none;">
            <div class="key-row"><span>Move Tool Horizontal:</span> <span><span class="key-cap">A</span> <span class="key-cap">D</span> / <span class="key-cap">◄</span> <span class="key-cap">►</span></span></div>
            <div class="key-row"><span>Move Tool Forward/Back:</span> <span><span class="key-cap">W</span> <span class="key-cap">S</span> / <span class="key-cap">▲</span> <span class="key-cap">▼</span></span></div>
            <div class="key-row"><span>Lift / Lower Tool Vertical:</span> <span><span class="key-cap">Q</span> / <span class="key-cap">E</span></span></div>
            <div class="key-row"><span>Perform Action / Heat / Unscrew:</span> <span>Hold <span class="key-cap">SPACEBAR</span> or Click</span></div>
        </div>
    </div>
</div>

<div class="instruction-card">
    <div class="instruction-text" id="instructionText">Position Tool & hold <b>SPACEBAR</b> over joints to apply heat.</div>
    <div class="heat-meter" id="heatMeter"><div class="heat-fill" id="heatFill"></div></div>
</div>

<div id="webgl-container"></div>

<script>
let scene, camera, renderer, controls;
let raycaster = new THREE.Raycaster();
let mouse = new THREE.Vector2();
let dragPlane = new THREE.Plane();
let planeIntersect = new THREE.Vector3();

// Keyboard Control State
const keyState = {};

// App Logic State
let activeModule = 'solder'; 
let solderStep = 1; 
let reformatStep = 1; 
let disStep = 1; // Disassembly & Assembly Steps 1 through 13

let heatProgress = 0;
let bootProgress = 0;
let toolProgress = 0;
let isHoldingTool = false;
let activeTool = null;
let targetReticle = null;

// Dynamic Canvas Screen Texture
let phoneCanvas, phoneCtx, phoneTexture;

// FX Particles & Heat Indicators
let smokeParticles = [];
let heatLight;
let ironTipMesh;

// Materials
const silverMat = new THREE.MeshStandardMaterial({ color: 0xd1d5db, metalness: 0.9, roughness: 0.2 });
const darkMetalMat = new THREE.MeshStandardMaterial({ color: 0x111827, metalness: 0.7, roughness: 0.5 });
const goldMat = new THREE.MeshStandardMaterial({ color: 0xd97706, metalness: 0.8, roughness: 0.3 });
const subBoardGreen = new THREE.MeshStandardMaterial({ color: 0x15803d, roughness: 0.3 });
const nokiaNavyMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 });
const keypadGlassMat = new THREE.MeshStandardMaterial({ color: 0x38bdf8, roughness: 0.2, transparent: true, opacity: 0.85 });

// 3D Objects
let heatGun, oldMicroUsb, newMicroUsb, solderIron, subBoard;
let phoneGroup, phoneScreen, btnPower, btnVolUp, btnVolDown;
let solderJoints = [];

// Module 3 Classic Handheld Nokia Phone Parts & Tools
let pryTool, screwdriverTool;
let handPhoneGroup;
let nokiaSimTray, nokiaBackPlate, nokiaInnerFrame, nokiaMainBoard, nokiaBattery, nokiaBatteryCable, nokiaFlexCables, nokiaGlueAdhesive;
let nokiaFrontBezel, nokiaKeypad, nokiaDisplayScreen;
let nokiaScrews = [];

init();
animate();

function init() {
    const container = document.getElementById('webgl-container');

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x090d16);

    camera = new THREE.PerspectiveCamera(40, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.set(0, 10, 12);

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    container.appendChild(renderer.domElement);

    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;

    // Lights
    scene.add(new THREE.AmbientLight(0xffffff, 0.8));
    const dirLight = new THREE.DirectionalLight(0xffffff, 1.2);
    dirLight.position.set(5, 12, 8);
    dirLight.castShadow = true;
    scene.add(dirLight);

    heatLight = new THREE.PointLight(0xff5500, 0, 3);
    scene.add(heatLight);

    buildDynamicScreenCanvas();
    buildAllModuleObjects();
    createTargetReticle();
    initSmokeParticles();

    window.addEventListener('resize', onWindowResize);
    window.addEventListener('pointerdown', onPointerDown);
    window.addEventListener('pointermove', onPointerMove);
    window.addEventListener('pointerup', onPointerUp);

    window.addEventListener('keydown', (e) => {
        keyState[e.code] = true;
        if(['Space', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.code)) {
            e.preventDefault();
        }
    });

    window.addEventListener('keyup', (e) => {
        keyState[e.code] = false;
        if(['Space', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.code)) {
            e.preventDefault();
        }
    });
}

function buildDynamicScreenCanvas() {
    phoneCanvas = document.createElement('canvas');
    phoneCanvas.width = 512;
    phoneCanvas.height = 1024;
    phoneCtx = phoneCanvas.getContext('2d');
    
    phoneTexture = new THREE.CanvasTexture(phoneCanvas);
    phoneTexture.minFilter = THREE.LinearFilter;
    phoneTexture.magFilter = THREE.LinearFilter;
    
    drawScreenContent('POWER_OFF');
}

function drawScreenContent(state, extraParam = '') {
    const w = phoneCanvas.width;
    const h = phoneCanvas.height;

    if(state === 'POWER_OFF') {
        phoneCtx.fillStyle = '#020617';
        phoneCtx.fillRect(0, 0, w, h);
    } 
    else if(state === 'BOOTING') {
        phoneCtx.fillStyle = '#000000';
        phoneCtx.fillRect(0, 0, w, h);
        
        phoneCtx.fillStyle = '#22c55e';
        phoneCtx.beginPath();
        phoneCtx.arc(w/2, h/2 - 80, 70, Math.PI, 0, false);
        phoneCtx.fill();

        phoneCtx.fillStyle = '#ffffff';
        phoneCtx.font = 'bold 42px Plus Jakarta Sans, sans-serif';
        phoneCtx.textAlign = 'center';
        phoneCtx.fillText('ANDROID', w/2, h/2 + 30);
        phoneCtx.font = '20px monospace';
        phoneCtx.fillStyle = '#94a3b8';
        phoneCtx.fillText('Powered by NC3 Servicing OS', w/2, h/2 + 80);
    } 
    else if(state === 'RECOVERY_MENU') {
        phoneCtx.fillStyle = '#0b0f19';
        phoneCtx.fillRect(0, 0, w, h);

        phoneCtx.fillStyle = '#38bdf8';
        phoneCtx.font = 'bold 24px monospace';
        phoneCtx.textAlign = 'left';
        phoneCtx.fillText('Android Recovery', 30, 70);
        phoneCtx.font = '16px monospace';
        phoneCtx.fillStyle = '#64748b';
        phoneCtx.fillText('Universal Servicing Build', 30, 98);
        
        phoneCtx.strokeStyle = '#334155';
        phoneCtx.lineWidth = 2;
        phoneCtx.beginPath();
        phoneCtx.moveTo(30, 115);
        phoneCtx.lineTo(w - 30, 115);
        phoneCtx.stroke();

        const menuOptions = [
            'Reboot system now',
            'Reboot to bootloader',
            'Apply update from ADB',
            'Wipe data/factory reset',
            'Wipe cache partition',
            'Mount /system'
        ];

        const highlightedIdx = (extraParam === 'CONFIRM_STEP') ? -1 : 3;

        menuOptions.forEach((item, idx) => {
            const yPos = 160 + (idx * 55);
            if(idx === highlightedIdx) {
                phoneCtx.fillStyle = '#ef4444';
                phoneCtx.fillRect(20, yPos - 32, w - 40, 44);
                phoneCtx.fillStyle = '#ffffff';
                phoneCtx.font = 'bold 22px monospace';
            } else {
                phoneCtx.fillStyle = '#94a3b8';
                phoneCtx.font = '20px monospace';
            }
            phoneCtx.fillText(item, 35, yPos);
        });

        if(extraParam === 'CONFIRM_STEP') {
            phoneCtx.fillStyle = '#000000';
            phoneCtx.fillRect(20, 520, w - 40, 260);
            phoneCtx.strokeStyle = '#ef4444';
            phoneCtx.strokeRect(20, 520, w - 40, 260);

            phoneCtx.fillStyle = '#f87171';
            phoneCtx.font = 'bold 20px monospace';
            phoneCtx.fillText('Wipe all user data?', 40, 560);
            phoneCtx.fillText('THIS CANNOT BE UNDONE!', 40, 590);

            phoneCtx.fillStyle = '#334155';
            phoneCtx.fillRect(35, 620, w - 70, 42);
            phoneCtx.fillStyle = '#ffffff';
            phoneCtx.fillText('No', 50, 648);

            phoneCtx.fillStyle = '#dc2626';
            phoneCtx.fillRect(35, 680, w - 70, 48);
            phoneCtx.fillStyle = '#ffffff';
            phoneCtx.font = 'bold 22px monospace';
            phoneCtx.fillText('> Factory data reset / Yes', 50, 712);
        }
    }
    else if(state === 'WIPING') {
        phoneCtx.fillStyle = '#020617';
        phoneCtx.fillRect(0, 0, w, h);

        phoneCtx.fillStyle = '#f59e0b';
        phoneCtx.font = '22px monospace';
        phoneCtx.textAlign = 'left';
        phoneCtx.fillText('-- Erasing all user data...', 30, 90);
        phoneCtx.fillText('Formatting /data...', 30, 140);
        phoneCtx.fillText('Formatting /cache...', 30, 190);
        phoneCtx.fillText('Formatting /metadata...', 30, 240);

        if(extraParam === 'DONE') {
            phoneCtx.fillStyle = '#22c55e';
            phoneCtx.fillText('Data wipe complete.', 30, 320);
            phoneCtx.fillText('System ready to reboot.', 30, 370);
            
            phoneCtx.fillStyle = '#22c55e';
            phoneCtx.fillRect(20, 440, w - 40, 50);
            phoneCtx.fillStyle = '#000000';
            phoneCtx.font = 'bold 22px monospace';
            phoneCtx.fillText('> Reboot system now', 35, 473);
        }
    }
    else if(state === 'HOME_SCREEN') {
        phoneCtx.fillStyle = '#1e1b4b';
        phoneCtx.fillRect(0, 0, w, h);

        phoneCtx.fillStyle = '#ffffff';
        phoneCtx.font = 'bold 80px Plus Jakarta Sans';
        phoneCtx.textAlign = 'center';
        phoneCtx.fillText('12:00', w/2, 220);

        phoneCtx.font = 'bold 22px Plus Jakarta Sans';
        phoneCtx.fillStyle = '#38bdf8';
        phoneCtx.fillText('Welcome to Restored Device', w/2, 290);

        for(let r=0; r<4; r++) {
            for(let c=0; c<3; c++) {
                phoneCtx.fillStyle = `hsl(${ (r*3+c)*30 + 180 }, 70%, 50%)`;
                phoneCtx.beginPath();
                phoneCtx.roundRect(65 + (c*135), 400 + (r*135), 85, 85, 20);
                phoneCtx.fill();
            }
        }
    }

    if(phoneTexture) phoneTexture.needsUpdate = true;
}

function createTargetReticle() {
    const geo = new THREE.RingGeometry(0.2, 0.28, 32);
    geo.rotateX(-Math.PI / 2);
    const mat = new THREE.MeshBasicMaterial({ color: 0x38bdf8, side: THREE.DoubleSide });
    targetReticle = new THREE.Mesh(geo, mat);
    targetReticle.position.set(0.6, 0.25, 2.2);
    targetReticle.visible = false;
    scene.add(targetReticle);
}

function initSmokeParticles() {
    const pGeo = new THREE.SphereGeometry(0.06, 8, 8);
    const pMat = new THREE.MeshBasicMaterial({ color: 0x94a3b8, transparent: true, opacity: 0 });

    for (let i = 0; i < 20; i++) {
        const p = new THREE.Mesh(pGeo, pMat.clone());
        p.visible = false;
        scene.add(p);
        smokeParticles.push({ mesh: p, life: 0, maxLife: 30 + Math.random() * 20 });
    }
}

function spawnSmokeParticle(pos) {
    const pObj = smokeParticles.find(p => !p.mesh.visible);
    if (pObj) {
        pObj.mesh.position.copy(pos);
        pObj.mesh.position.x += (Math.random() - 0.5) * 0.2;
        pObj.mesh.position.z += (Math.random() - 0.5) * 0.2;
        pObj.mesh.visible = true;
        pObj.mesh.material.opacity = 0.6;
        pObj.life = 0;
    }
}

function updateSmokeParticles() {
    smokeParticles.forEach(pObj => {
        if (pObj.mesh.visible) {
            pObj.life++;
            pObj.mesh.position.y += 0.03;
            pObj.mesh.scale.addScalar(0.02);
            pObj.mesh.material.opacity = 0.6 * (1 - (pObj.life / pObj.maxLife));

            if (pObj.life >= pObj.maxLife) {
                pObj.mesh.visible = false;
                pObj.mesh.scale.set(1, 1, 1);
            }
        }
    });
}

function buildSubBoardShape() {
    const shape = new THREE.Shape();
    shape.moveTo(-2, -4);
    shape.lineTo(2, -4);
    shape.lineTo(2, -1);
    shape.absarc(0, 0, 1.8, 0, Math.PI, false);
    shape.lineTo(-2, 2.5);
    shape.lineTo(-1.2, 3.8);
    shape.lineTo(1.5, 3.8);
    shape.lineTo(2, 2);
    shape.lineTo(-2, -4);

    const extrudeSettings = { depth: 0.2, bevelEnabled: true, bevelSegments: 2, steps: 1, bevelSize: 0.05, bevelThickness: 0.05 };
    const geometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);
    geometry.rotateX(Math.PI / 2);
    return geometry;
}

function createMicroUsbPortMesh(isDamaged = false) {
    const portGroup = new THREE.Group();
    const shellMat = isDamaged ? 
        new THREE.MeshStandardMaterial({ color: 0x4b5563, metalness: 0.6, roughness: 0.6 }) : silverMat;

    const shell = new THREE.Mesh(new THREE.BoxGeometry(2.2, 0.5, 1.6), shellMat);
    portGroup.add(shell);

    const tongue = new THREE.Mesh(new THREE.BoxGeometry(1.6, 0.15, 1.2), darkMetalMat);
    tongue.position.set(0, -0.05, 0.2);
    portGroup.add(tongue);

    const legGeo = new THREE.BoxGeometry(0.3, 0.4, 0.5);
    const legs = [{x: -1.2, z: -0.4}, {x: 1.2, z: -0.4}, {x: -1.2, z: 0.4}, {x: 1.2, z: 0.4}];

    legs.forEach(l => {
        const leg = new THREE.Mesh(legGeo, shellMat);
        leg.position.set(l.x, -0.2, l.z);
        portGroup.add(leg);
    });

    for(let i=0; i<5; i++) {
        const pin = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.1, 0.4), goldMat);
        pin.position.set(-0.6 + (i * 0.3), -0.2, -0.9);
        portGroup.add(pin);
    }

    return portGroup;
}

function buildAllModuleObjects() {
    // ================= MODULE 1 =================
    subBoard = new THREE.Group();
    const boardMesh = new THREE.Mesh(buildSubBoardShape(), subBoardGreen);
    boardMesh.receiveShadow = true;
    subBoard.add(boardMesh);

    const holeGeo = new THREE.CylinderGeometry(0.3, 0.3, 0.25, 16);
    const holeMat = new THREE.MeshStandardMaterial({ color: 0xd97706, metalness: 0.8 });
    
    const h1 = new THREE.Mesh(holeGeo, holeMat);
    h1.position.set(0.2, 0.1, -1.2);
    const h2 = new THREE.Mesh(holeGeo, holeMat);
    h2.position.set(0.8, 0.1, 2.8);
    subBoard.add(h1, h2);

    scene.add(subBoard);

    oldMicroUsb = createMicroUsbPortMesh(true);
    oldMicroUsb.position.set(0.6, 0.35, 2.2);
    scene.add(oldMicroUsb);

    newMicroUsb = createMicroUsbPortMesh(false);
    newMicroUsb.position.set(4, 0.35, 2.2);
    newMicroUsb.visible = false;
    scene.add(newMicroUsb);

    const padLocations = [
        {x: -0.6, z: 1.8}, {x: 1.8, z: 1.8}, {x: -0.6, z: 2.6}, {x: 1.8, z: 2.6},
        {x: 0.0, z: 1.3}, {x: 0.3, z: 1.3}, {x: 0.6, z: 1.3}, {x: 0.9, z: 1.3}, {x: 1.2, z: 1.3}
    ];

    padLocations.forEach((loc) => {
        const joint = new THREE.Mesh(
            new THREE.SphereGeometry(0.12, 12, 12),
            new THREE.MeshStandardMaterial({ color: 0x52525b, roughness: 0.7 })
        );
        joint.scale.set(1.2, 0.3, 1.2);
        joint.position.set(loc.x, 0.22, loc.z);
        subBoard.add(joint);
        solderJoints.push(joint);
    });

    // Tools
    heatGun = new THREE.Group();
    const gunNozzle = new THREE.Mesh(new THREE.CylinderGeometry(0.2, 0.4, 2, 16), silverMat);
    const gunHandle = new THREE.Mesh(new THREE.CylinderGeometry(0.4, 0.4, 3, 16), new THREE.MeshStandardMaterial({ color: 0xd97706 }));
    gunHandle.position.y = 2.2;
    heatGun.add(gunNozzle, gunHandle);
    heatGun.rotation.x = Math.PI / 3;
    heatGun.position.set(2.5, 3.5, 2);
    scene.add(heatGun);

    solderIron = new THREE.Group();
    ironTipMesh = new THREE.Mesh(new THREE.ConeGeometry(0.08, 1.2, 16), silverMat.clone());
    const ironHandle = new THREE.Mesh(new THREE.CylinderGeometry(0.3, 0.3, 3, 16), new THREE.MeshStandardMaterial({ color: 0x0284c7 }));
    ironHandle.position.y = 2;
    solderIron.add(ironTipMesh, ironHandle);
    solderIron.rotation.x = Math.PI / 4;
    solderIron.position.set(3, 3, 2);
    solderIron.visible = false;
    scene.add(solderIron);

    pryTool = new THREE.Group();
    const pryHead = new THREE.Mesh(new THREE.BoxGeometry(0.4, 0.08, 0.8), new THREE.MeshStandardMaterial({ color: 0x0284c7 }));
    const pryHandle = new THREE.Mesh(new THREE.CylinderGeometry(0.15, 0.15, 2.5), new THREE.MeshStandardMaterial({ color: 0x38bdf8 }));
    pryHandle.position.z = -1.2;
    pryHandle.rotation.x = Math.PI / 2;
    pryTool.add(pryHead, pryHandle);
    pryTool.position.set(0, -20, 0);
    pryTool.visible = false;
    scene.add(pryTool);

    screwdriverTool = new THREE.Group();
    const driverTip = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, 1.2), silverMat);
    const driverHandle = new THREE.Mesh(new THREE.CylinderGeometry(0.25, 0.2, 2.2), new THREE.MeshStandardMaterial({ color: 0xef4444 }));
    driverHandle.position.y = 1.5;
    screwdriverTool.add(driverTip, driverHandle);
    screwdriverTool.position.set(0, -20, 0);
    screwdriverTool.visible = false;
    scene.add(screwdriverTool);

    // ================= MODULE 2: REFORMAT PHONE =================
    phoneGroup = new THREE.Group();
    
    const phoneBody = new THREE.Mesh(
        new THREE.BoxGeometry(2.8, 0.25, 5.6), 
        new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.2, metalness: 0.8 })
    );

    const phoneScreenMat = new THREE.MeshBasicMaterial({ map: phoneTexture });
    phoneScreen = new THREE.Mesh(new THREE.PlaneGeometry(2.6, 5.3), phoneScreenMat);
    phoneScreen.rotation.x = -Math.PI / 2;
    phoneScreen.position.y = 0.13;

    btnPower = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.15, 0.7), new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.8 }));
    btnPower.position.set(1.45, 0, 0.2);

    btnVolUp = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.15, 0.5), new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.8 }));
    btnVolUp.position.set(-1.45, 0, 0.6);

    btnVolDown = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.15, 0.5), new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.8 }));
    btnVolDown.position.set(-1.45, 0, -0.1);

    phoneGroup.add(phoneBody, phoneScreen, btnPower, btnVolUp, btnVolDown);
    phoneGroup.position.set(0, -20, 0);
    scene.add(phoneGroup);

    // ================= MODULE 3: AUTHENTIC CLASSIC NOKIA HANDHELD PHONE =================
    handPhoneGroup = new THREE.Group();

    // 1. SIM Tray Slot
    nokiaSimTray = new THREE.Mesh(new THREE.BoxGeometry(0.4, 0.1, 0.8), silverMat);
    nokiaSimTray.position.set(1.25, 0.15, -1);

    // 2. Front Housing & Keypad Bezel (Front Face)
    nokiaFrontBezel = new THREE.Group();
    const frontFrame = new THREE.Mesh(new THREE.BoxGeometry(2.6, 0.1, 5.4), nokiaNavyMat);
    
    // Screen Display Glass
    nokiaDisplayScreen = new THREE.Mesh(
        new THREE.BoxGeometry(2.0, 0.05, 1.8),
        new THREE.MeshStandardMaterial({ color: 0x0284c7, roughness: 0.1 })
    );
    nokiaDisplayScreen.position.set(0, 0.06, -1.2);

    // Classic T9 Physical Keypad Grid
    nokiaKeypad = new THREE.Group();
    for(let r=0; r<4; r++) {
        for(let c=0; c<3; c++) {
            const keyBtn = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.08, 0.35), keypadGlassMat);
            keyBtn.position.set(-0.65 + (c * 0.65), 0.06, 0.5 + (r * 0.5));
            nokiaKeypad.add(keyBtn);
        }
    }
    // D-Pad Directional Key Center
    const navPad = new THREE.Mesh(new THREE.BoxGeometry(0.8, 0.09, 0.5), silverMat);
    navPad.position.set(0, 0.06, -0.1);
    nokiaKeypad.add(navPad);

    nokiaFrontBezel.add(frontFrame, nokiaDisplayScreen, nokiaKeypad);
    nokiaFrontBezel.position.set(0, 0.7, 0);

    // 3. Outer Back Cover Shell
    nokiaBackPlate = new THREE.Mesh(new THREE.BoxGeometry(2.65, 0.1, 5.45), nokiaNavyMat);
    nokiaBackPlate.position.set(0, 0.6, 0);

    // 4. Perimeter Adhesive Strip
    nokiaGlueAdhesive = new THREE.Mesh(
        new THREE.BoxGeometry(2.55, 0.04, 5.35), 
        new THREE.MeshStandardMaterial({ color: 0xef4444, transparent: true, opacity: 0.6 })
    );
    nokiaGlueAdhesive.position.set(0, 0.52, 0);

    // 5. Inner Structural Chassis & Camera Shield
    nokiaInnerFrame = new THREE.Mesh(new THREE.BoxGeometry(2.5, 0.18, 5.3), darkMetalMat);
    nokiaInnerFrame.position.set(0, 0.4, 0);

    // Screws (16 Internal Fasteners)
    const screwGeo = new THREE.CylinderGeometry(0.07, 0.07, 0.12, 8);
    for(let r=0; r<4; r++) {
        for(let c=0; c<4; c++) {
            const screw = new THREE.Mesh(screwGeo, silverMat);
            screw.position.set(-0.95 + (c * 0.63), 0.51, -2.1 + (r * 1.4));
            nokiaInnerFrame.add(screw);
            nokiaScrews.push(screw);
        }
    }

    // 6. Battery Connector Power Clip
    nokiaBatteryCable = new THREE.Mesh(new THREE.BoxGeometry(0.45, 0.12, 0.25), goldMat);
    nokiaBatteryCable.position.set(0.6, 0.32, 0.2);

    // 7. Removable Lithium Battery Pack
    nokiaBattery = new THREE.Mesh(
        new THREE.BoxGeometry(2.1, 0.22, 3.0), 
        new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 })
    );
    nokiaBattery.position.set(0, 0.25, -0.5);

    // 8. Motherboard Ribbon Flex Cables
    nokiaFlexCables = new THREE.Group();
    const flex1 = new THREE.Mesh(new THREE.BoxGeometry(0.3, 0.06, 1.6), goldMat);
    flex1.position.set(-0.75, 0.28, 0.8);
    const flex2 = new THREE.Mesh(new THREE.BoxGeometry(0.3, 0.06, 1.6), goldMat);
    flex2.position.set(0.75, 0.28, 0.8);
    nokiaFlexCables.add(flex1, flex2);

    // 9. Main Logic Circuit Board
    nokiaMainBoard = new THREE.Mesh(new THREE.BoxGeometry(2.45, 0.18, 5.2), subBoardGreen);
    nokiaMainBoard.position.set(0, 0.1, 0);

    handPhoneGroup.add(
        nokiaSimTray, nokiaFrontBezel, nokiaBackPlate, nokiaGlueAdhesive, 
        nokiaInnerFrame, nokiaBatteryCable, nokiaBattery, nokiaFlexCables, nokiaMainBoard
    );
    handPhoneGroup.position.set(0, -20, 0);
    scene.add(handPhoneGroup);
}

function switchModule(mod) {
    activeModule = mod;
    document.querySelectorAll('.mod-btn').forEach(btn => btn.classList.remove('active'));

    subBoard.position.y = -20;
    oldMicroUsb.position.y = -20;
    newMicroUsb.position.y = -20;
    heatGun.visible = false;
    solderIron.visible = false;
    pryTool.visible = false;
    screwdriverTool.visible = false;
    targetReticle.visible = false;
    phoneGroup.position.y = -20;
    handPhoneGroup.position.y = -20;

    const heatMeter = document.getElementById('heatMeter');
    const mod1Keys = document.getElementById('mod1Keys');
    const mod2Keys = document.getElementById('mod2Keys');
    const mod3Keys = document.getElementById('mod3Keys');

    if(mod === 'solder') {
        event.currentTarget.classList.add('active');
        subBoard.position.set(0, 0, 0);
        oldMicroUsb.position.set(0.6, 0.35, 2.2);
        heatGun.visible = true;
        heatGun.position.set(2.5, 3.5, 2);
        targetReticle.visible = true;
        solderStep = 1;
        heatProgress = 0;
        document.getElementById('heatFill').style.width = '0%';
        heatMeter.style.display = "block";
        mod1Keys.style.display = "block";
        mod2Keys.style.display = "none";
        mod3Keys.style.display = "none";
        
        document.getElementById('stepBadge').innerText = "MODULE 1 / STEP 1";
        document.getElementById('stepGuide').innerHTML = "Position <b>Hot Air Gun</b> over damaged port using <b>WASD Keys</b> or Mouse, then hold <span class='key-cap'>SPACEBAR</span> to melt solder.";
        document.getElementById('instructionText').innerHTML = "Align nozzle over port and hold <b>SPACEBAR</b> to apply heat.";
    } 
    else if(mod === 'reformat') {
        event.currentTarget.classList.add('active');
        phoneGroup.position.set(0, 0.2, 0);
        reformatStep = 1;
        bootProgress = 0;
        drawScreenContent('POWER_OFF');
        
        heatMeter.style.display = "block";
        document.getElementById('heatFill').style.width = '0%';
        mod1Keys.style.display = "none";
        mod2Keys.style.display = "block";
        mod3Keys.style.display = "none";

        document.getElementById('stepBadge').innerText = "STEP 1: RECOVERY BOOT";
        document.getElementById('stepGuide').innerHTML = "Turn off device. Hold <b>Power</b> (<span class='key-cap'>P</span>) + <b>Volume Up / Down</b> (<span class='key-cap'>▲</span>/<span class='key-cap'>▼</span>) together until recovery logo appears.";
        document.getElementById('instructionText').innerHTML = "Hold <b>POWER + VOLUME UP/DOWN</b> until progress bar fills.";
    }
    else if(mod === 'disassemble') {
        event.currentTarget.classList.add('active');
        handPhoneGroup.position.set(0, 0, 0);
        disStep = 1;
        toolProgress = 0;

        resetNokiaPhoneState();

        heatMeter.style.display = "block";
        document.getElementById('heatFill').style.width = '0%';
        mod1Keys.style.display = "none";
        mod2Keys.style.display = "none";
        mod3Keys.style.display = "block";

        updateDisassemblyGuide();
    }
}

function resetNokiaPhoneState() {
    nokiaSimTray.position.set(1.25, 0.15, -1);
    nokiaFrontBezel.position.set(0, 0.7, 0);
    nokiaFrontBezel.visible = true;
    nokiaBackPlate.position.set(0, 0.6, 0);
    nokiaBackPlate.visible = true;
    nokiaGlueAdhesive.position.set(0, 0.52, 0);
    nokiaGlueAdhesive.visible = true;
    nokiaInnerFrame.position.set(0, 0.4, 0);
    nokiaInnerFrame.visible = true;
    nokiaBatteryCable.position.set(0.6, 0.32, 0.2);
    nokiaBatteryCable.visible = true;
    nokiaBattery.position.set(0, 0.25, -0.5);
    nokiaBattery.visible = true;
    nokiaFlexCables.position.set(0, 0, 0);
    nokiaFlexCables.visible = true;
    nokiaMainBoard.position.set(0, 0.1, 0);

    nokiaScrews.forEach(s => s.visible = true);
}

function updateDisassemblyGuide() {
    targetReticle.visible = false;
    heatGun.visible = false;
    pryTool.visible = false;
    screwdriverTool.visible = false;

    if (disStep === 1) {
        document.getElementById('stepBadge').innerText = "DISASSEMBLY / STEP 1: SIM TRAY";
        document.getElementById('stepGuide').innerHTML = "Power off the handheld phone completely and extract the <b>SIM card tray</b> from the side slot.";
        document.getElementById('instructionText').innerHTML = "Click SIM Tray or hold <b>SPACEBAR</b> to extract.";
    } 
    else if (disStep === 2) {
        heatGun.visible = true;
        heatGun.position.set(0, 2.5, 0);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "DISASSEMBLY / STEP 2: HEAT ADHESIVE";
        document.getElementById('stepGuide').innerHTML = "Heat the <b>back plate</b> using heat gun (<span class='key-cap'>A</span><span class='key-cap'>D</span> / <span class='key-cap'>W</span><span class='key-cap'>S</span> / <span class='key-cap'>Q</span><span class='key-cap'>E</span> + hold <span class='key-cap'>SPACEBAR</span>) to soften glue.";
        document.getElementById('instructionText').innerHTML = "Hold <b>SPACEBAR</b> over back plate to soften adhesive.";
    }
    else if (disStep === 3) {
        pryTool.visible = true;
        pryTool.position.set(1.4, 0.8, 0);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "DISASSEMBLY / STEP 3: REMOVE BACK COVER";
        document.getElementById('stepGuide').innerHTML = "Pry off the <b>back cover plate</b> gently with thin plastic pry tool (<span class='key-cap'>WASD</span> + hold <span class='key-cap'>SPACEBAR</span>).";
        document.getElementById('instructionText').innerHTML = "Position pry tool along edge and hold <b>SPACEBAR</b> to detach cover.";
    }
    else if (disStep === 4) {
        screwdriverTool.visible = true;
        screwdriverTool.position.set(0, 2, 0);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "DISASSEMBLY / STEP 4: UNSCREW SHIELDS";
        document.getElementById('stepGuide').innerHTML = "Remove <b>16 Phillips screws</b> holding inner frame and camera shields using screwdriver.";
        document.getElementById('instructionText').innerHTML = "Position screwdriver and hold <b>SPACEBAR</b> to unfasten screws.";
    }
    else if (disStep === 5) {
        pryTool.visible = true;
        pryTool.position.set(0.6, 0.8, 0.2);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "DISASSEMBLY / STEP 5: DISCONNECT BATTERY";
        document.getElementById('stepGuide').innerHTML = "Disconnect <b>battery cable</b> first before touching internal connectors to prevent short circuits.";
        document.getElementById('instructionText').innerHTML = "Use pry tool or click battery connector to unplug power cable.";
    }
    else if (disStep === 6) {
        pryTool.visible = true;
        pryTool.position.set(-0.8, 0.8, 0.8);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "DISASSEMBLY / STEP 6: UNPLUG FLEX CABLES";
        document.getElementById('stepGuide').innerHTML = "Unplug <b>flex cables</b> for screen, keypad, subboard, and camera with plastic spudger tool.";
        document.getElementById('instructionText').innerHTML = "Hold <b>SPACEBAR</b> over flex connectors to disconnect them.";
    }
    else if (disStep === 7) {
        pryTool.visible = true;
        pryTool.position.set(0, 0.8, -0.6);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "DISASSEMBLY / STEP 7: LIFT COMPONENTS";
        document.getElementById('stepGuide').innerHTML = "Lift out internal components (<b>main board, keypad bezel, speakers, battery</b>) from frame.";
        document.getElementById('instructionText').innerHTML = "Hold <b>SPACEBAR</b> or click frame to extract main internal modules.";
    }
    // ================= ASSEMBLY STEPS =================
    else if (disStep === 8) {
        document.getElementById('stepBadge').innerText = "ASSEMBLY / STEP 8: RE-SEAT MAIN BOARD";
        document.getElementById('stepGuide').innerHTML = "Place <b>main board and internal components</b> back into middle frame in reverse order.";
        document.getElementById('instructionText').innerHTML = "Click subboard/chassis to seat main logic board in middle frame.";
    }
    else if (disStep === 9) {
        pryTool.visible = true;
        pryTool.position.set(-0.8, 0.8, 0.8);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "ASSEMBLY / STEP 9: RECONNECT FLEX CABLES";
        document.getElementById('stepGuide').innerHTML = "Reconnect all <b>flex ribbon cables</b> and snap firmly into sockets on the main board.";
        document.getElementById('instructionText').innerHTML = "Position tool and hold <b>SPACEBAR</b> to snap flex cables into sockets.";
    }
    else if (disStep === 10) {
        pryTool.visible = true;
        pryTool.position.set(0.6, 0.8, 0.2);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "ASSEMBLY / STEP 10: RECONNECT BATTERY";
        document.getElementById('stepGuide').innerHTML = "Plug in <b>battery cable</b> as the very last step before closing inner housing.";
        document.getElementById('instructionText').innerHTML = "Hold <b>SPACEBAR</b> over connector to securely attach battery power cable.";
    }
    else if (disStep === 11) {
        screwdriverTool.visible = true;
        screwdriverTool.position.set(0, 2, 0);
        targetReticle.visible = true;
        document.getElementById('stepBadge').innerText = "ASSEMBLY / STEP 11: TIGHTEN SCREWS";
        document.getElementById('stepGuide').innerHTML = "Secure inner shields and tighten all <b>16 Phillips screws</b> back into correct slots.";
        document.getElementById('instructionText').innerHTML = "Position screwdriver and hold <b>SPACEBAR</b> to tighten frame screws.";
    }
    else if (disStep === 12) {
        document.getElementById('stepBadge').innerText = "ASSEMBLY / STEP 12: APPLY ADHESIVE";
        document.getElementById('stepGuide').innerHTML = "Apply fresh <b>double-sided adhesive tape</b> around edges of frame for back cover.";
        document.getElementById('instructionText').innerHTML = "Click frame perimeter to apply sticky adhesive seal.";
    }
    else if (disStep === 13) {
        document.getElementById('stepBadge').innerText = "ASSEMBLY / STEP 13: SEAL & SIM TRAY";
        document.getElementById('stepGuide').innerHTML = "Press <b>back plate cover</b> down tightly and reinsert <b>SIM tray</b> to finish assembly.";
        document.getElementById('instructionText').innerHTML = "Click phone body to press back cover down & reinsert SIM tray.";
    }
}

function advanceDisassemblyStep() {
    toolProgress = 0;
    document.getElementById('heatFill').style.width = '0%';
    disStep++;

    if (disStep === 3) nokiaBackPlate.position.y = 2.2;
    if (disStep === 4) nokiaGlueAdhesive.visible = false;
    if (disStep === 5) nokiaScrews.forEach(s => s.visible = false);
    if (disStep === 6) nokiaBatteryCable.position.y = 1.2;
    if (disStep === 7) nokiaFlexCables.position.y = 1.5;
    if (disStep === 8) {
        nokiaBattery.position.y = 1.8;
        nokiaMainBoard.position.y = 1.2;
        nokiaInnerFrame.position.y = 2.2;
        nokiaFrontBezel.position.y = 2.8;
    }
    if (disStep === 9) {
        nokiaMainBoard.position.y = 0.1;
        nokiaInnerFrame.position.y = 0.4;
        nokiaBattery.position.y = 0.25;
        nokiaFrontBezel.position.y = 0.7;
    }
    if (disStep === 10) nokiaFlexCables.position.y = 0;
    if (disStep === 11) nokiaBatteryCable.position.y = 0.32;
    if (disStep === 12) nokiaScrews.forEach(s => s.visible = true);
    if (disStep === 13) nokiaGlueAdhesive.visible = true;
    if (disStep > 13) {
        nokiaBackPlate.position.y = 0.6;
        nokiaSimTray.position.set(1.25, 0.15, -1);
        targetReticle.visible = false;
        screwdriverTool.visible = false;
        pryTool.visible = false;
        heatGun.visible = false;
        document.getElementById('stepBadge').innerText = "DISASSEMBLY & ASSEMBLY COMPLETE";
        document.getElementById('stepGuide').innerHTML = "Full repair sequence successfully executed! Handheld device assembled & functional.";
        document.getElementById('instructionText').innerHTML = "<b>DISASSEMBLY & REASSEMBLY COMPLETE!</b> 🎉";
        return;
    }

    updateDisassemblyGuide();
}

function updateReformatLogic() {
    if (activeModule !== 'reformat') return;

    if (reformatStep === 1) {
        const isPowerPressed = keyState['KeyP'] || keyState['powerClicked'];
        const isVolPressed = keyState['ArrowUp'] || keyState['ArrowDown'] || keyState['volUpClicked'] || keyState['volDownClicked'];

        if (isPowerPressed && isVolPressed) {
            bootProgress += 1.6;
            document.getElementById('heatFill').style.width = Math.min(bootProgress, 100) + '%';

            btnPower.position.x = 1.40;
            if(keyState['ArrowUp'] || keyState['volUpClicked']) btnVolUp.position.x = -1.40;
            if(keyState['ArrowDown'] || keyState['volDownClicked']) btnVolDown.position.x = -1.40;

            if (bootProgress >= 100) {
                reformatStep = 2;
                bootProgress = 0;
                document.getElementById('heatFill').style.width = '0%';
                drawScreenContent('RECOVERY_MENU');

                document.getElementById('stepBadge').innerText = "STEP 2: NAVIGATE MENU";
                document.getElementById('stepGuide').innerHTML = "Release buttons when Recovery Menu appears. Highlight <b>Wipe data/factory reset</b> and tap to confirm.";
                document.getElementById('instructionText').innerHTML = "Click highlighted <b>Wipe data/factory reset</b> option.";
            }
        } else {
            btnPower.position.x = 1.45;
            btnVolUp.position.x = -1.45;
            btnVolDown.position.x = -1.45;

            if (bootProgress > 0) {
                bootProgress = Math.max(0, bootProgress - 2);
                document.getElementById('heatFill').style.width = bootProgress + '%';
            }
        }
    }
}

function updateKeyboardInput() {
    let tool = null;
    if (activeModule === 'solder') {
        tool = (solderStep === 1) ? heatGun : (solderStep === 3 ? solderIron : null);
    } else if (activeModule === 'disassemble') {
        if (disStep === 2) tool = heatGun;
        else if ([3, 5, 6, 7, 9, 10].includes(disStep)) tool = pryTool;
        else if ([4, 11].includes(disStep)) tool = screwdriverTool;
    }

    if (!tool || !tool.visible) return;

    const moveSpeed = 0.08;

    // Direct Directional & Keyboard Mapping (A/D = Left/Right, W/S or UP/DOWN = Forward/Back)
    if (keyState['KeyA'] || keyState['ArrowLeft']) tool.position.x -= moveSpeed;
    if (keyState['KeyD'] || keyState['ArrowRight']) tool.position.x += moveSpeed;
    if (keyState['KeyW'] || keyState['ArrowUp']) tool.position.z -= moveSpeed;
    if (keyState['KeyS'] || keyState['ArrowDown']) tool.position.z += moveSpeed;

    // Vertical Height adjustment
    if (keyState['KeyQ']) tool.position.y += moveSpeed;
    if (keyState['KeyE']) tool.position.y = Math.max(0.4, tool.position.y - moveSpeed);

    if (targetReticle) {
        targetReticle.position.set(tool.position.x, 0.25, tool.position.z);
    }

    const isSpacePressed = keyState['Space'] || keyState['Spacebar'];

    if (isSpacePressed) {
        if (targetReticle) targetReticle.material.color.setHex(0xef4444);

        if (activeModule === 'solder') {
            heatLight.position.copy(tool.position);
            heatLight.intensity = 2.5 + Math.random() * 0.5;

            if (Math.random() > 0.3) spawnSmokeParticle(tool.position);

            if (solderStep === 1) {
                let dx = tool.position.x - oldMicroUsb.position.x;
                let dz = tool.position.z - oldMicroUsb.position.z;
                let dist2D = Math.sqrt(dx * dx + dz * dz);

                if (dist2D < 1.8 && tool.position.y < 4.5) {
                    heatProgress += 1.0;
                    document.getElementById('heatFill').style.width = Math.min(heatProgress, 100) + '%';

                    if (heatProgress >= 100) {
                        solderStep = 2;
                        scene.remove(oldMicroUsb);
                        heatGun.visible = false;
                        newMicroUsb.visible = true;
                        heatLight.intensity = 0;

                        document.getElementById('stepBadge').innerText = "MODULE 1 / STEP 2";
                        document.getElementById('stepGuide').innerHTML = "Old port removed! Click directly on target pads to seat new Micro-USB port.";
                        document.getElementById('instructionText').innerHTML = "Click sub-board pads to seat replacement port.";
                    }
                }
            } else if (solderStep === 3) {
                ironTipMesh.material.color.setHex(0xf97316);

                solderJoints.forEach((joint) => {
                    let worldPos = new THREE.Vector3();
                    joint.getWorldPosition(worldPos);
                    
                    let dx = tool.position.x - worldPos.x;
                    let dz = tool.position.z - worldPos.z;
                    let dist2D = Math.sqrt(dx * dx + dz * dz);

                    if (dist2D < 0.6 && tool.position.y < 2.5) {
                        joint.material.color.setHex(0xf3f4f6);
                        joint.material.roughness = 0.1;
                        joint.userData.soldered = true;

                        let allSoldered = solderJoints.every(j => j.userData.soldered);
                        if (allSoldered) {
                            solderIron.visible = false;
                            targetReticle.visible = false;
                            heatLight.intensity = 0;
                            document.getElementById('instructionText').innerHTML = "<b>MICRO-USB REPLACEMENT COMPLETE!</b> All joints soldered securely. 🎉";
                        }
                    }
                });
            }
        }
        else if (activeModule === 'disassemble') {
            toolProgress += 1.8;
            document.getElementById('heatFill').style.width = Math.min(toolProgress, 100) + '%';

            if (disStep === 2) {
                heatLight.position.copy(tool.position);
                heatLight.intensity = 2.0;
                if (Math.random() > 0.4) spawnSmokeParticle(tool.position);
            }

            if (disStep === 4 || disStep === 11) {
                screwdriverTool.rotation.y += 0.2;
            }

            if (toolProgress >= 100) {
                advanceDisassemblyStep();
            }
        }
    } else {
        if (targetReticle) targetReticle.material.color.setHex(0x38bdf8);
        heatLight.intensity = 0;
        if (solderStep === 3 && ironTipMesh) {
            ironTipMesh.material.color.setHex(0xd1d5db);
        }
    }
}

function onPointerDown(event) {
    mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
    mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
    raycaster.setFromCamera(mouse, camera);

    if (activeModule === 'solder') {
        if (solderStep === 1) {
            const intersects = raycaster.intersectObject(heatGun, true);
            if (intersects.length > 0) {
                isHoldingTool = true;
                activeTool = heatGun;
                controls.enabled = false;
                dragPlane.setFromNormalAndCoplanarPoint(camera.getWorldDirection(new THREE.Vector3()).negate(), heatGun.position);
            }
        } else if (solderStep === 2) {
            const intersects = raycaster.intersectObject(subBoard, true);
            if(intersects.length > 0) {
                newMicroUsb.position.set(0.6, 0.35, 2.2);
                solderStep = 3;
                solderIron.visible = true;
                targetReticle.visible = true;
                
                document.getElementById('stepBadge').innerText = "MODULE 1 / STEP 3";
                document.getElementById('stepGuide').innerHTML = "Position <b>Soldering Iron</b> over each joint using <b>WASD</b> or Mouse, then hold <span class='key-cap'>SPACEBAR</span> to solder pins.";
                document.getElementById('instructionText').innerHTML = "Solder anchor legs and 5 Micro-USB terminal pins.";
            }
        } else if (solderStep === 3) {
            const intersects = raycaster.intersectObject(solderIron, true);
            if(intersects.length > 0) {
                isHoldingTool = true;
                activeTool = solderIron;
                controls.enabled = false;
                dragPlane.setFromNormalAndCoplanarPoint(camera.getWorldDirection(new THREE.Vector3()).negate(), solderIron.position);
            }
        }
    }
    else if (activeModule === 'reformat') {
        if(raycaster.intersectObject(btnPower).length > 0) keyState['powerClicked'] = true;
        if(raycaster.intersectObject(btnVolUp).length > 0) keyState['volUpClicked'] = true;
        if(raycaster.intersectObject(btnVolDown).length > 0) keyState['volDownClicked'] = true;

        const screenHits = raycaster.intersectObject(phoneScreen);
        if (screenHits.length > 0) {
            if (reformatStep === 2) {
                reformatStep = 3;
                drawScreenContent('RECOVERY_MENU', 'CONFIRM_STEP');
                document.getElementById('stepBadge').innerText = "STEP 3: CONFIRM RESET";
                document.getElementById('stepGuide').innerHTML = "Select <b>Factory data reset</b> or <b>Yes</b> to start erasing all data completely.";
                document.getElementById('instructionText').innerHTML = "Click <b>Factory data reset / Yes</b> to authorize wipe.";
            } 
            else if (reformatStep === 3) {
                reformatStep = 4;
                drawScreenContent('WIPING');
                document.getElementById('stepBadge').innerText = "STEP 4: ERASING DATA";
                document.getElementById('stepGuide').innerHTML = "Formatting storage, cache partitions, and system files...";
                document.getElementById('instructionText').innerHTML = "Formatting data partitions...";

                setTimeout(() => {
                    drawScreenContent('WIPING', 'DONE');
                    reformatStep = 5;
                    document.getElementById('stepBadge').innerText = "STEP 5: REBOOT SYSTEM";
                    document.getElementById('stepGuide').innerHTML = "Choose <b>Reboot system now</b> using Power Button once wipe is complete.";
                    document.getElementById('instructionText').innerHTML = "Click <b>Reboot system now</b> option to restart phone.";
                }, 2500);
            }
            else if (reformatStep === 5) {
                reformatStep = 6;
                drawScreenContent('POWER_OFF');
                document.getElementById('instructionText').innerHTML = "Restarting device...";

                setTimeout(() => {
                    drawScreenContent('BOOTING');
                }, 1200);

                setTimeout(() => {
                    drawScreenContent('HOME_SCREEN');
                    document.getElementById('stepBadge').innerText = "STEP 6: COMPLETE";
                    document.getElementById('stepGuide').innerHTML = "Mobile reformatting successful! Device restored to clean factory state.";
                    document.getElementById('instructionText').innerHTML = "<b>REFORMAT COMPLETE!</b> Device booted to initial setup screen. 🎉";
                }, 3800);
            }
        }
    }
    else if (activeModule === 'disassemble') {
        if (disStep === 1 && raycaster.intersectObject(nokiaSimTray).length > 0) {
            nokiaSimTray.position.set(2.2, 0.15, -1);
            advanceDisassemblyStep();
        } else if (disStep === 8 && raycaster.intersectObject(nokiaMainBoard).length > 0) {
            advanceDisassemblyStep();
        } else if (disStep === 12 && raycaster.intersectObject(handPhoneGroup, true).length > 0) {
            advanceDisassemblyStep();
        } else if (disStep === 13 && raycaster.intersectObject(handPhoneGroup, true).length > 0) {
            advanceDisassemblyStep();
        }
    }
}

function onPointerMove(event) {
    mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
    mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

    if (isHoldingTool && activeTool) {
        raycaster.setFromCamera(mouse, camera);
        if (raycaster.ray.intersectPlane(dragPlane, planeIntersect)) {
            activeTool.position.copy(planeIntersect);
            if (targetReticle) targetReticle.position.set(activeTool.position.x, 0.25, activeTool.position.z);
        }
    }
}

function onPointerUp() {
    isHoldingTool = false;
    activeTool = null;
    controls.enabled = true;
    keyState['powerClicked'] = false;
    keyState['volUpClicked'] = false;
    keyState['volDownClicked'] = false;
}

function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
}

function animate() {
    requestAnimationFrame(animate);
    updateKeyboardInput();
    updateReformatLogic();
    updateSmokeParticles();
    controls.update();
    renderer.render(scene, camera);
}
</script>
</body>
</html>