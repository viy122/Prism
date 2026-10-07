const fs = require('fs');
const path = require('path');
const os = require('os');
const assert = require('assert');
const ELK = require(path.join(os.tmpdir(), 'prism-elk.bundled.cjs'));
const dir = __dirname;
const data = JSON.parse(fs.readFileSync(path.join(dir, '../prism-dfd-v2/flows.json'), 'utf8'));
const esc = x => String(x).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
function wrap(text, max) {
  const lines = [''];
  for (const word of text.split(' ')) {
    if ((lines.at(-1) + ' ' + word).trim().length > max) lines.push(word);
    else lines[lines.length - 1] = (lines.at(-1) + ' ' + word).trim();
  }
  return lines;
}
const font = 17;
const leading = 22;
const nodes = [
  ...data.entities.map((name,i) => ({ id:'E'+i, name, type:'entity', width:255, height:100 })),
  ...data.processes.map((name,i) => ({ id:'P'+(i+1), name, type:'process', width:270, height:145 })),
  ...data.stores.map((name,i) => ({ id:'D'+(i+1), name, type:'store', width:290, height:100 }))
];
const graph = {
  id: 'root',
  layoutOptions: {
    'elk.algorithm':'layered',
    'elk.direction':'RIGHT',
    'elk.edgeRouting':'ORTHOGONAL',
    'elk.padding':'[top=40,left=40,bottom=40,right=40]',
    'elk.spacing.nodeNode':'110',
    'elk.spacing.edgeNode':'35',
    'elk.spacing.edgeEdge':'25',
    'elk.spacing.labelLabel':'25',
    'elk.spacing.edgeLabel':'12',
    'elk.layered.spacing.nodeNodeBetweenLayers':'170',
    'elk.layered.spacing.edgeNodeBetweenLayers':'35',
    'elk.layered.spacing.edgeEdgeBetweenLayers':'25',
    'elk.layered.nodePlacement.strategy':'NETWORK_SIMPLEX',
    'elk.layered.crossingMinimization.strategy':'LAYER_SWEEP',
    'elk.layered.thoroughness':'35',
    'elk.layered.mergeEdges':'false'
  },
  children: nodes.map(({id,width,height}) => ({id,width,height})),
  edges: data.edges.map((e,i) => {
    const lines = wrap(e.label, 30);
    return {id:'F'+String(i+1).padStart(2,'0'), sources:[e.from], targets:[e.to], labels:[{
      text:e.label, width:Math.max(...lines.map(s=>s.length))*font*.57+18,
      height:lines.length*leading+14,
      layoutOptions:{'elk.edgeLabels.placement':'CENTER'}
    }]};
  })
};

Promise.resolve(JSON.parse(fs.readFileSync(path.join(dir, 'manual-layout.json'), 'utf8'))).then(g => {
  const positioned = g.children.map(n => ({...nodes.find(o=>o.id===n.id),...n}));
  const segments=[];
  for (const e of g.edges) {
    assert(e.sections?.length === 1, 'Expected one continuous route: '+e.id);
    const s=e.sections[0], points=[s.startPoint,...(s.bendPoints||[]),s.endPoint];
    e.points=points;
    for (let i=1;i<points.length;i++) {
      const a=points[i-1],b=points[i];
      assert(Math.abs(a.x-b.x)<.01 || Math.abs(a.y-b.y)<.01, 'Non-orthogonal segment');
      segments.push({a,b,edge:e.id,h:Math.abs(a.y-b.y)<.01});
    }
  }
  const overlaps=[];
  for (let i=0;i<segments.length;i++) for(let j=i+1;j<segments.length;j++) {
    const a=segments[i],b=segments[j];
    if(a.edge===b.edge || a.h!==b.h) continue;
    const same=a.h ? Math.abs(a.a.y-b.a.y)<.01 : Math.abs(a.a.x-b.a.x)<.01;
    const axis=a.h?'x':'y';
    const overlap=Math.min(Math.max(a.a[axis],a.b[axis]),Math.max(b.a[axis],b.b[axis]))-Math.max(Math.min(a.a[axis],a.b[axis]),Math.min(b.a[axis],b.b[axis]));
    if(same && overlap>1) overlaps.push([a.edge,b.edge,overlap]);
  }
  const labelBoxes=g.edges.flatMap(e=>e.labels.map(l=>({...l,edge:e.id})));
  const labelOverlaps=[];
  for(let i=0;i<labelBoxes.length;i++)for(let j=i+1;j<labelBoxes.length;j++){
    const a=labelBoxes[i],b=labelBoxes[j];
    if(a.x<b.x+b.width && a.x+a.width>b.x && a.y<b.y+b.height && a.y+a.height>b.y) labelOverlaps.push([a.edge,b.edge]);
  }
  if(overlaps.length) console.log('Shared segments:',JSON.stringify(overlaps));
  assert.equal(labelOverlaps.length,0,'Arrow labels overlap');
  const pad=65, header=170, footer=90;
  const W=g.width+pad*2,H=g.height+header+footer;
  const pngWidth=5600,pngHeight=Math.ceil(pngWidth*H/W);
  const svg=[];
  svg.push(`<svg xmlns="http://www.w3.org/2000/svg" width="${pngWidth}" height="${pngHeight}" viewBox="0 0 ${W} ${H}" role="img" aria-label="PRISM Level 1 DFD, eight processes, three stores, six unique user boxes">`);
  svg.push('<rect width="100%" height="100%" fill="white"/>');
  svg.push(`<text x="${W/2}" y="68" text-anchor="middle" font-family="Arial" font-size="42" font-weight="700">PRISM — Level 1 Data Flow Diagram</text>`);
  svg.push(`<text x="${W/2}" y="112" text-anchor="middle" font-family="Arial" font-size="23">8 processes · 3 data stores · 6 user roles</text>`);
  svg.push(`<g transform="translate(${pad},${header})" fill="none" stroke="#111" stroke-width="1.9" stroke-linejoin="round">`);
  for(const s of segments.filter(s=>!s.h)) svg.push(`<path d="M ${s.a.x},${s.a.y} L ${s.b.x},${s.b.y}"/>`);
  let jumps=0;
  for(const s of segments.filter(s=>s.h)) {
    const min=Math.min(s.a.x,s.b.x),max=Math.max(s.a.x,s.b.x),y=s.a.y,r=6;
    const cross=segments.filter(v=>!v.h && v.edge!==s.edge && v.a.x>min+r+1 && v.a.x<max-r-1 && y>Math.min(v.a.y,v.b.y)+1 && y<Math.max(v.a.y,v.b.y)-1).map(v=>v.a.x).sort((a,b)=>a-b);
    const unique=[...new Set(cross)];
    let d=`M ${min},${y}`;
    for(const x of unique) {d+=` L ${x-r},${y} Q ${x},${y-2*r} ${x+r},${y}`;jumps++;}
    d+=` L ${max},${y}`;
    svg.push(`<path d="${d}"/>`);
    for(const x of unique) {
      const bridge=`M ${x-r},${y} Q ${x},${y-2*r} ${x+r},${y}`;
      svg.push(`<path d="${bridge}" stroke="white" stroke-width="6"/><path d="${bridge}"/>`);
    }
  }
  for(const e of g.edges) {
    const a=e.points.at(-2),b=e.points.at(-1),dx=b.x-a.x,dy=b.y-a.y,len=Math.hypot(dx,dy),ux=dx/len,uy=dy/len;
    svg.push(`<polygon points="${b.x},${b.y} ${b.x-ux*11-uy*4},${b.y-uy*11+ux*4} ${b.x-ux*11+uy*4},${b.y-uy*11-ux*4}" fill="#111" stroke="none"/>`);
  }
  for(const e of g.edges) for(const l of e.labels) {
    const lines=wrap(l.text,30);
    svg.push(`<g class="flow" data-id="${e.id}" data-from="${e.sources[0]}" data-to="${e.targets[0]}"><title>${esc(l.text)}</title><rect x="${l.x}" y="${l.y}" width="${l.width}" height="${l.height}" fill="white" stroke="none"/>`);
    lines.forEach((line,i)=>svg.push(`<text x="${l.x+l.width/2}" y="${l.y+10+font+i*leading}" text-anchor="middle" font-family="Arial" font-size="${font}" fill="#111" stroke="none">${esc(line)}</text>`));
    svg.push('</g>');
  }
  function text(name,cx,cy,size=22,max=25) {
    const lines=wrap(name,max),lh=size*1.22;
    return lines.map((line,i)=>`<text x="${cx}" y="${cy+(i-(lines.length-1)/2)*lh+size*.35}" text-anchor="middle" font-family="Arial" font-size="${size}" font-weight="600" fill="#111" stroke="none">${esc(line)}</text>`).join('');
  }
  for(const n of positioned) {
    const {x,y,width:w,height:h}=n;
    svg.push(`<g class="node" data-id="${n.id}"><title>${esc(n.name)}</title>`);
    if(n.type==='store') {
      svg.push(`<path d="M ${x+w},${y} H ${x} V ${y+h} H ${x+w} M ${x+52},${y} V ${y+h}" fill="white"/>`);
      svg.push(text(n.id,x+26,y+h/2,20),text(n.name,x+52+(w-52)/2,y+h/2,20,22));
    } else {
      svg.push(`<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${n.type==='process'?14:0}" fill="white"/>`);
      if(n.type==='process') {
        svg.push(`<path d="M ${x},${y+38} H ${x+w}"/>`,text(n.id.slice(1)+'.0',x+w/2,y+19,20),text(n.name,x+w/2,y+38+(h-38)/2,22,24));
      } else svg.push(text(n.name,x+w/2,y+h/2,22,23));
    }
    svg.push('</g>');
  }
  svg.push('</g>');
  svg.push(`<text x="${pad}" y="${H-30}" font-family="Arial" font-size="18" fill="#444">Line bridges indicate separate flows, not junctions. External flow labels match the approved context diagram.</text></svg>`);
  fs.writeFileSync(path.join(dir,'prism-dfd.svg'),svg.join('\n'));
  fs.writeFileSync(path.join(dir,'preview.html'),'<!doctype html><html><head><meta charset="utf-8"><style>html,body{margin:0;background:white}svg{display:block}</style></head><body>'+svg.join('\n')+'</body></html>');
  fs.writeFileSync(path.join(dir,'flows.json'),JSON.stringify(data,null,2));
  fs.writeFileSync(path.join(dir,'layout.json'),JSON.stringify(g,null,2));
  fs.writeFileSync(path.join(dir,'render-info.json'),JSON.stringify({width:pngWidth,height:pngHeight,nodes:positioned.length,flows:g.edges.length,lineBridges:jumps,lineOverlaps:overlaps,labelOverlaps},null,2));
  console.log(JSON.stringify({width:pngWidth,height:pngHeight,nodes:positioned.length,flows:g.edges.length,lineBridges:jumps}));
}).catch(e=>{console.error(e);process.exit(1)});
