const fs = require('fs');
const path = require('path');
const assert = require('assert');
const Viz = require(path.join(require('os').tmpdir(), 'prism-viz-standalone.cjs'));
const dir = __dirname;
const prompt = fs.readFileSync(path.join(dir, 'generation-prompt.txt'), 'utf8');
const entities = ['Office Head / Dean', 'Budget Office', 'Procurement Office', 'Chancellor', 'Vice Chancellor', 'Cashier'];
const processes = ['Manage Budget Proposals', 'Manage Market Scoping', 'Manage Reviews and Signatures', 'Manage Procurement Plans', 'Manage Procurement Documents', 'Manage Delivery and Payment Records', 'Monitor Procurement Progress', 'Generate Reports'];
const stores = ['Budget Proposal Records', 'Market Scoping Records', 'Procurement Records', 'Review and Signature Records', 'Delivery and Payment Records', 'Report Archives'];
const id = name => entities.includes(name) ? 'E' + entities.indexOf(name) : /^\d\.0$/.test(name) ? 'P' + name[0] : name;
const edges = prompt.split('\n').map(line => line.match(/^(.+?) -> (.+?) : (.+)$/)).filter(m => m && (entities.includes(m[1]) || /^(?:[1-8]\.0|D[1-6])$/.test(m[1]))).map(m => ({ from: id(m[1]), to: id(m[2]), label: m[3] }));
assert.equal(edges.length, 63);
assert.equal(edges.filter(e => e.from.startsWith('E') || e.to.startsWith('E')).length, 32);
const wrap = (text, width = 27) => {
  let lines = [''];
  for (const word of text.split(' ')) {
    if (lines[lines.length - 1].length + word.length + 1 > width) lines.push(word);
    else lines[lines.length - 1] += (lines[lines.length - 1] ? ' ' : '') + word;
  }
  return lines.join('\n');
};
const q = JSON.stringify;
const dot = [
  'digraph PRISM {',
  'graph [rankdir=TB, bgcolor="white", pad=0.5, nodesep=0.65, ranksep=0.9, splines=spline, outputorder=edgesfirst, fontname="Arial", fontsize=28, labelloc=t, label="PRISM — Level 1 Data Flow Diagram\n8 processes | 6 data stores | 6 user roles"];',
  'node [fontname="Arial", fontsize=16, color="#18202b", penwidth=1.4, margin="0.18,0.12"];',
  'edge [fontname="Arial", fontsize=12, arrowsize=0.8, color="#56616e", fontcolor="#18202b", penwidth=1.05];',
  ...entities.map((name, i) => 'E' + i + ' [shape=box, style=filled, fillcolor="#f1f5f9", label=' + q(wrap(name)) + '];'),
  ...processes.map((name, i) => 'P' + (i+1) + ' [shape=Mrecord, style=filled, fillcolor="#ffffff", label=' + q('{' + (i+1) + '.0|' + wrap(name, 23) + '}') + '];'),
  ...stores.map((name, i) => 'D' + (i+1) + ' [shape=plaintext, label=<<TABLE BORDER="0" CELLBORDER="1" CELLSPACING="0" CELLPADDING="9"><TR><TD>D' + (i+1) + '</TD><TD SIDES="TB">' + wrap(name, 22).split('\n').join('<BR/>') + '</TD></TR></TABLE>>];'),
  ...edges.map(e => e.from + ' -> ' + e.to + ' [label=' + q(wrap(e.label, 28)) + '];'),
  '}'
].join('\n');
fs.writeFileSync(path.join(dir, 'prism-dfd.dot'), dot);
fs.writeFileSync(path.join(dir, 'flows.json'), JSON.stringify({entities, processes, stores, edges}, null, 2));
Viz.instance().then(viz => {
  const svg = viz.renderString(dot, {format:'svg', engine:'dot'});
  for (let i=1; i<=8; i++) assert(svg.includes('<title>P'+i+'</title>'));
  for (let i=1; i<=6; i++) assert(svg.includes('<title>D'+i+'</title>'));
  assert.equal((svg.match(/class="edge"/g) || []).length, 63);
  const dims = svg.match(/viewBox="0\.00 0\.00 ([\d.]+) ([\d.]+)"/);
  const width=4800, height=Math.ceil(width*Number(dims[2])/Number(dims[1]));
  const sized = svg.replace(/width="[\d.]+pt" height="[\d.]+pt"/, 'width="'+width+'px" height="'+height+'px"');
  fs.writeFileSync(path.join(dir, 'prism-dfd.svg'), sized);
  fs.writeFileSync(path.join(dir, 'preview.html'), '<!doctype html><html><head><meta charset="utf-8"><style>html,body{margin:0;background:white}svg{display:block}</style></head><body>'+sized+'</body></html>');
  console.log(JSON.stringify({width,height,processes:8,stores:6,externalFlows:32,totalFlows:63}));
});
