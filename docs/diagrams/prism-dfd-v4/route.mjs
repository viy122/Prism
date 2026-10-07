import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import {fileURLToPath,pathToFileURL} from 'node:url';
const {AvoidLib}=await import(pathToFileURL(path.join(os.tmpdir(),'prism-libavoid.mjs')));
await AvoidLib.load(path.join(os.tmpdir(),'libavoid.wasm'));
const A=AvoidLib.getInstance(),dir=path.dirname(fileURLToPath(import.meta.url));
const data=JSON.parse(fs.readFileSync(path.join(dir,'../prism-dfd-v2/flows.json'),'utf8'));
const wrap=(text,max=30)=>{const ls=[''];for(const w of text.split(' ')){if((ls.at(-1)+' '+w).trim().length>max)ls.push(w);else ls[ls.length-1]=(ls.at(-1)+' '+w).trim();}return ls;};
const xy={E0:[100,100],E1:[2300,100],E3:[3000,100],E4:[3650,100],E2:[100,1530],E5:[3650,1530],P1:[800,530],P2:[1720,530],P3:[2720,530],D1:[1250,1070],D3:[3120,1070],P4:[800,1640],P5:[1800,1640],P6:[2820,1640],D2:[1950,2160],P7:[1200,2720],P8:[2720,2720]};
const nodes=Object.entries(xy).map(([id,[x,y]])=>({id,x,y,width:id[0]==='D'?380:id[0]==='P'?350:300,height:id[0]==='P'?160:120}));
const lookup=Object.fromEntries(nodes.map(n=>[n.id,n]));
const router=new A.Router(2);
for(const [key,val] of Object.entries({segmentPenalty:40,crossingPenalty:800,fixedSharedPathPenalty:1000,shapeBufferDistance:10,idealNudgingDistance:14}))router.setRoutingParameter(A.RoutingParameter[key],val);
for(const key of ['nudgeOrthogonalSegmentsConnectedToShapes','penaliseOrthogonalSharedPathsAtConnEnds','nudgeOrthogonalTouchingColinearSegments'])router.setRoutingOption(A.RoutingOption[key],true);
const shapes={};
for(const n of nodes)shapes[n.id]=new A.ShapeRef(router,new A.Rectangle(new A.Point(n.x,n.y),new A.Point(n.x+n.width,n.y+n.height)));
const ends={};
for(let i=0;i<data.edges.length;i++)for(const kind of ['from','to']){
 const e=data.edges[i],n=lookup[e[kind]],other=lookup[e[kind==='from'?'to':'from']];
 const dx=other.x+other.width/2-(n.x+n.width/2),dy=other.y+other.height/2-(n.y+n.height/2);
 let side=Math.abs(dx)>Math.abs(dy)*1.25?(dx>0?'R':'L'):(dy>0?'B':'T');
 if(n.id[0]==='E')side=n.id==='E2'?'R':n.id==='E5'?'L':'B';
 const key=n.id+side;(ends[key]??=[]).push({i,kind,n,other,side});
}
const pins={};
let pid=1;
for(const entries of Object.values(ends)){
 entries.sort((a,b)=>['L','R'].includes(a.side)?a.other.y-b.other.y||a.i-b.i:a.other.x-b.other.x||a.i-b.i);
 entries.forEach((e,j)=>{const t=(j+1)/(entries.length+1),x=e.side==='L'?0:e.side==='R'?1:t,y=e.side==='T'?0:e.side==='B'?1:t;
   const pin=new A.ShapeConnectionPin(shapes[e.n.id],pid,x,y,true,0,({T:1,B:2,L:4,R:8})[e.side]);pin.setExclusive(true);
   pins[e.i+e.kind]={shape:shapes[e.n.id],pid};pid++;
 });
}
const conns=data.edges.map((e,i)=>{const s=pins[i+'from'],t=pins[i+'to'];const c=new A.ConnRef(router,new A.ConnEnd(s.shape,s.pid),new A.ConnEnd(t.shape,t.pid));c.setHateCrossings(true);return c;});
router.processTransaction();
const points=c=>{const r=c.displayRoute();return Array.from({length:r.size()},(_,i)=>({x:r.at(i).x,y:r.at(i).y}));};
const inflate=(b,m)=>({x:b.x-m,y:b.y-m,width:b.width+2*m,height:b.height+2*m});
const intersect=(a,b)=>a.x<b.x+b.width&&a.x+a.width>b.x&&a.y<b.y+b.height&&a.y+a.height>b.y;
const boxes=nodes.map(n=>inflate(n,25)),labels=[];
const initial=conns.map(points);
const order=data.edges.map((e,i)=>i).sort((a,b)=>initial[a].length-initial[b].length);
for(const i of order){
 const ls=wrap(data.edges[i].label),w=Math.max(...ls.map(s=>s.length))*17*.57+18,h=ls.length*22+14,candidates=[];
 const ps=initial[i];
 for(let j=1;j<ps.length;j++){
   const a=ps[j-1],b=ps[j];
   if(Math.abs(a.y-b.y)<.01&&Math.abs(a.x-b.x)>w+45){
     const low=Math.min(a.x,b.x)+20,high=Math.max(a.x,b.x)-w-20;
     for(let x=low;x<=high;x+=40)for(const off of [22,80,150])candidates.push({x,y:a.y-h-off,width:w,height:h,base:a.y,score:Math.abs((x+w/2)-(a.x+b.x)/2)+off});
   }
 }
 const s=lookup[data.edges[i].from],t=lookup[data.edges[i].to],cx=(s.x+t.x)/2,cy=(s.y+t.y)/2;
 for(let y=300;y<2680;y+=110)for(let x=460;x<3650-w;x+=130)candidates.push({x,y,width:w,height:h,base:y+h+22,score:2500+Math.abs(x-cx)+Math.abs(y-cy)});
 candidates.sort((a,b)=>a.score-b.score);
 const b=candidates.find(b=>!boxes.some(o=>intersect(inflate(b,20),o)) && !nodes.some(o=>intersect({x:b.x-10,y:b.base-18,width:b.width+20,height:36},inflate(o,15))));
 if(!b)throw Error('No label slot '+i);
 labels[i]={x:b.x,y:b.y,width:w,height:h,text:data.edges[i].label};boxes.push(inflate(b,20));
 new A.ShapeRef(router,new A.Rectangle(new A.Point(b.x-8,b.y-6),new A.Point(b.x+w+8,b.y+h+6)));
 b.base=b.y+h+22;
 const cp=new A.CheckpointVector();
 const left={x:b.x,y:b.base},right={x:b.x+w,y:b.base};
 const distance=(a,b)=>Math.abs(a.x-b.x)+Math.abs(a.y-b.y);
 const forward=distance(ps[0],left)+distance(right,ps.at(-1))<=distance(ps[0],right)+distance(left,ps.at(-1));
 for(const point of (forward?[left,right]:[right,left]))cp.push_back(new A.Checkpoint(new A.Point(point.x,point.y)));
 conns[i].setRoutingCheckpoints(cp);
}
router.processTransaction();
const final=conns.map(points);
let minX=Math.min(0,...final.flat().map(p=>p.x)),minY=Math.min(0,...final.flat().map(p=>p.y));
const ox=40-minX,oy=40-minY;
const children=nodes.map(n=>({...n,x:n.x+ox,y:n.y+oy}));
const edges=data.edges.map((e,i)=>{const ps=final[i].map(p=>({x:p.x+ox,y:p.y+oy}));return{id:'F'+String(i+1).padStart(2,'0'),sources:[e.from],targets:[e.to],labels:[{...labels[i],x:labels[i].x+ox,y:labels[i].y+oy}],sections:[{startPoint:ps[0],endPoint:ps.at(-1),bendPoints:ps.slice(1,-1)}]};});
const width=Math.max(4100,...final.flat().map(p=>p.x))+ox+40,height=Math.max(2920,...final.flat().map(p=>p.y))+oy+40;
fs.writeFileSync(path.join(dir,'manual-layout.json'),JSON.stringify({width,height,children,edges},null,2));
console.log(JSON.stringify({width,height,edges:edges.length}));
