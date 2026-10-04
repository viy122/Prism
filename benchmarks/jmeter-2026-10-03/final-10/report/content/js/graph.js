/*
   Licensed to the Apache Software Foundation (ASF) under one or more
   contributor license agreements.  See the NOTICE file distributed with
   this work for additional information regarding copyright ownership.
   The ASF licenses this file to You under the Apache License, Version 2.0
   (the "License"); you may not use this file except in compliance with
   the License.  You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
$(document).ready(function() {

    $(".click-title").mouseenter( function(    e){
        e.preventDefault();
        this.style.cursor="pointer";
    });
    $(".click-title").mousedown( function(event){
        event.preventDefault();
    });

    // Ugly code while this script is shared among several pages
    try{
        refreshHitsPerSecond(true);
    } catch(e){}
    try{
        refreshResponseTimeOverTime(true);
    } catch(e){}
    try{
        refreshResponseTimePercentiles();
    } catch(e){}
});


var responseTimePercentilesInfos = {
        data: {"result": {"minY": 308.0, "minX": 0.0, "maxY": 655.0, "series": [{"data": [[0.0, 308.0], [0.1, 308.0], [0.2, 308.0], [0.3, 308.0], [0.4, 308.0], [0.5, 308.0], [0.6, 308.0], [0.7, 308.0], [0.8, 308.0], [0.9, 308.0], [1.0, 353.0], [1.1, 353.0], [1.2, 353.0], [1.3, 353.0], [1.4, 353.0], [1.5, 353.0], [1.6, 353.0], [1.7, 353.0], [1.8, 353.0], [1.9, 353.0], [2.0, 355.0], [2.1, 355.0], [2.2, 355.0], [2.3, 355.0], [2.4, 355.0], [2.5, 355.0], [2.6, 355.0], [2.7, 355.0], [2.8, 355.0], [2.9, 355.0], [3.0, 383.0], [3.1, 383.0], [3.2, 383.0], [3.3, 383.0], [3.4, 383.0], [3.5, 383.0], [3.6, 383.0], [3.7, 383.0], [3.8, 383.0], [3.9, 383.0], [4.0, 390.0], [4.1, 390.0], [4.2, 390.0], [4.3, 390.0], [4.4, 390.0], [4.5, 390.0], [4.6, 390.0], [4.7, 390.0], [4.8, 390.0], [4.9, 390.0], [5.0, 390.0], [5.1, 390.0], [5.2, 390.0], [5.3, 390.0], [5.4, 390.0], [5.5, 390.0], [5.6, 390.0], [5.7, 390.0], [5.8, 390.0], [5.9, 390.0], [6.0, 396.0], [6.1, 396.0], [6.2, 396.0], [6.3, 396.0], [6.4, 396.0], [6.5, 396.0], [6.6, 396.0], [6.7, 396.0], [6.8, 396.0], [6.9, 396.0], [7.0, 404.0], [7.1, 404.0], [7.2, 404.0], [7.3, 404.0], [7.4, 404.0], [7.5, 404.0], [7.6, 404.0], [7.7, 404.0], [7.8, 404.0], [7.9, 404.0], [8.0, 405.0], [8.1, 405.0], [8.2, 405.0], [8.3, 405.0], [8.4, 405.0], [8.5, 405.0], [8.6, 405.0], [8.7, 405.0], [8.8, 405.0], [8.9, 405.0], [9.0, 406.0], [9.1, 406.0], [9.2, 406.0], [9.3, 406.0], [9.4, 406.0], [9.5, 406.0], [9.6, 406.0], [9.7, 406.0], [9.8, 406.0], [9.9, 406.0], [10.0, 408.0], [10.1, 408.0], [10.2, 408.0], [10.3, 408.0], [10.4, 408.0], [10.5, 408.0], [10.6, 408.0], [10.7, 408.0], [10.8, 408.0], [10.9, 408.0], [11.0, 412.0], [11.1, 412.0], [11.2, 412.0], [11.3, 412.0], [11.4, 412.0], [11.5, 412.0], [11.6, 412.0], [11.7, 412.0], [11.8, 412.0], [11.9, 412.0], [12.0, 415.0], [12.1, 415.0], [12.2, 415.0], [12.3, 415.0], [12.4, 415.0], [12.5, 415.0], [12.6, 415.0], [12.7, 415.0], [12.8, 415.0], [12.9, 415.0], [13.0, 417.0], [13.1, 417.0], [13.2, 417.0], [13.3, 417.0], [13.4, 417.0], [13.5, 417.0], [13.6, 417.0], [13.7, 417.0], [13.8, 417.0], [13.9, 417.0], [14.0, 417.0], [14.1, 417.0], [14.2, 417.0], [14.3, 417.0], [14.4, 417.0], [14.5, 417.0], [14.6, 417.0], [14.7, 417.0], [14.8, 417.0], [14.9, 417.0], [15.0, 418.0], [15.1, 418.0], [15.2, 418.0], [15.3, 418.0], [15.4, 418.0], [15.5, 418.0], [15.6, 418.0], [15.7, 418.0], [15.8, 418.0], [15.9, 418.0], [16.0, 421.0], [16.1, 421.0], [16.2, 421.0], [16.3, 421.0], [16.4, 421.0], [16.5, 421.0], [16.6, 421.0], [16.7, 421.0], [16.8, 421.0], [16.9, 421.0], [17.0, 423.0], [17.1, 423.0], [17.2, 423.0], [17.3, 423.0], [17.4, 423.0], [17.5, 423.0], [17.6, 423.0], [17.7, 423.0], [17.8, 423.0], [17.9, 423.0], [18.0, 430.0], [18.1, 430.0], [18.2, 430.0], [18.3, 430.0], [18.4, 430.0], [18.5, 430.0], [18.6, 430.0], [18.7, 430.0], [18.8, 430.0], [18.9, 430.0], [19.0, 432.0], [19.1, 432.0], [19.2, 432.0], [19.3, 432.0], [19.4, 432.0], [19.5, 432.0], [19.6, 432.0], [19.7, 432.0], [19.8, 432.0], [19.9, 432.0], [20.0, 434.0], [20.1, 434.0], [20.2, 434.0], [20.3, 434.0], [20.4, 434.0], [20.5, 434.0], [20.6, 434.0], [20.7, 434.0], [20.8, 434.0], [20.9, 434.0], [21.0, 435.0], [21.1, 435.0], [21.2, 435.0], [21.3, 435.0], [21.4, 435.0], [21.5, 435.0], [21.6, 435.0], [21.7, 435.0], [21.8, 435.0], [21.9, 435.0], [22.0, 435.0], [22.1, 435.0], [22.2, 435.0], [22.3, 435.0], [22.4, 435.0], [22.5, 435.0], [22.6, 435.0], [22.7, 435.0], [22.8, 435.0], [22.9, 435.0], [23.0, 435.0], [23.1, 435.0], [23.2, 435.0], [23.3, 435.0], [23.4, 435.0], [23.5, 435.0], [23.6, 435.0], [23.7, 435.0], [23.8, 435.0], [23.9, 435.0], [24.0, 435.0], [24.1, 435.0], [24.2, 435.0], [24.3, 435.0], [24.4, 435.0], [24.5, 435.0], [24.6, 435.0], [24.7, 435.0], [24.8, 435.0], [24.9, 435.0], [25.0, 435.0], [25.1, 435.0], [25.2, 435.0], [25.3, 435.0], [25.4, 435.0], [25.5, 435.0], [25.6, 435.0], [25.7, 435.0], [25.8, 435.0], [25.9, 435.0], [26.0, 440.0], [26.1, 440.0], [26.2, 440.0], [26.3, 440.0], [26.4, 440.0], [26.5, 440.0], [26.6, 440.0], [26.7, 440.0], [26.8, 440.0], [26.9, 440.0], [27.0, 442.0], [27.1, 442.0], [27.2, 442.0], [27.3, 442.0], [27.4, 442.0], [27.5, 442.0], [27.6, 442.0], [27.7, 442.0], [27.8, 442.0], [27.9, 442.0], [28.0, 443.0], [28.1, 443.0], [28.2, 443.0], [28.3, 443.0], [28.4, 443.0], [28.5, 443.0], [28.6, 443.0], [28.7, 443.0], [28.8, 443.0], [28.9, 443.0], [29.0, 447.0], [29.1, 447.0], [29.2, 447.0], [29.3, 447.0], [29.4, 447.0], [29.5, 447.0], [29.6, 447.0], [29.7, 447.0], [29.8, 447.0], [29.9, 447.0], [30.0, 449.0], [30.1, 449.0], [30.2, 449.0], [30.3, 449.0], [30.4, 449.0], [30.5, 449.0], [30.6, 449.0], [30.7, 449.0], [30.8, 449.0], [30.9, 449.0], [31.0, 451.0], [31.1, 451.0], [31.2, 451.0], [31.3, 451.0], [31.4, 451.0], [31.5, 451.0], [31.6, 451.0], [31.7, 451.0], [31.8, 451.0], [31.9, 451.0], [32.0, 452.0], [32.1, 452.0], [32.2, 452.0], [32.3, 452.0], [32.4, 452.0], [32.5, 452.0], [32.6, 452.0], [32.7, 452.0], [32.8, 452.0], [32.9, 452.0], [33.0, 454.0], [33.1, 454.0], [33.2, 454.0], [33.3, 454.0], [33.4, 454.0], [33.5, 454.0], [33.6, 454.0], [33.7, 454.0], [33.8, 454.0], [33.9, 454.0], [34.0, 455.0], [34.1, 455.0], [34.2, 455.0], [34.3, 455.0], [34.4, 455.0], [34.5, 455.0], [34.6, 455.0], [34.7, 455.0], [34.8, 455.0], [34.9, 455.0], [35.0, 456.0], [35.1, 456.0], [35.2, 456.0], [35.3, 456.0], [35.4, 456.0], [35.5, 456.0], [35.6, 456.0], [35.7, 456.0], [35.8, 456.0], [35.9, 456.0], [36.0, 457.0], [36.1, 457.0], [36.2, 457.0], [36.3, 457.0], [36.4, 457.0], [36.5, 457.0], [36.6, 457.0], [36.7, 457.0], [36.8, 457.0], [36.9, 457.0], [37.0, 458.0], [37.1, 458.0], [37.2, 458.0], [37.3, 458.0], [37.4, 458.0], [37.5, 458.0], [37.6, 458.0], [37.7, 458.0], [37.8, 458.0], [37.9, 458.0], [38.0, 459.0], [38.1, 459.0], [38.2, 459.0], [38.3, 459.0], [38.4, 459.0], [38.5, 459.0], [38.6, 459.0], [38.7, 459.0], [38.8, 459.0], [38.9, 459.0], [39.0, 459.0], [39.1, 459.0], [39.2, 459.0], [39.3, 459.0], [39.4, 459.0], [39.5, 459.0], [39.6, 459.0], [39.7, 459.0], [39.8, 459.0], [39.9, 459.0], [40.0, 459.0], [40.1, 459.0], [40.2, 459.0], [40.3, 459.0], [40.4, 459.0], [40.5, 459.0], [40.6, 459.0], [40.7, 459.0], [40.8, 459.0], [40.9, 459.0], [41.0, 460.0], [41.1, 460.0], [41.2, 460.0], [41.3, 460.0], [41.4, 460.0], [41.5, 460.0], [41.6, 460.0], [41.7, 460.0], [41.8, 460.0], [41.9, 460.0], [42.0, 462.0], [42.1, 462.0], [42.2, 462.0], [42.3, 462.0], [42.4, 462.0], [42.5, 462.0], [42.6, 462.0], [42.7, 462.0], [42.8, 462.0], [42.9, 462.0], [43.0, 463.0], [43.1, 463.0], [43.2, 463.0], [43.3, 463.0], [43.4, 463.0], [43.5, 463.0], [43.6, 463.0], [43.7, 463.0], [43.8, 463.0], [43.9, 463.0], [44.0, 463.0], [44.1, 463.0], [44.2, 463.0], [44.3, 463.0], [44.4, 463.0], [44.5, 463.0], [44.6, 463.0], [44.7, 463.0], [44.8, 463.0], [44.9, 463.0], [45.0, 464.0], [45.1, 464.0], [45.2, 464.0], [45.3, 464.0], [45.4, 464.0], [45.5, 464.0], [45.6, 464.0], [45.7, 464.0], [45.8, 464.0], [45.9, 464.0], [46.0, 464.0], [46.1, 464.0], [46.2, 464.0], [46.3, 464.0], [46.4, 464.0], [46.5, 464.0], [46.6, 464.0], [46.7, 464.0], [46.8, 464.0], [46.9, 464.0], [47.0, 466.0], [47.1, 466.0], [47.2, 466.0], [47.3, 466.0], [47.4, 466.0], [47.5, 466.0], [47.6, 466.0], [47.7, 466.0], [47.8, 466.0], [47.9, 466.0], [48.0, 467.0], [48.1, 467.0], [48.2, 467.0], [48.3, 467.0], [48.4, 467.0], [48.5, 467.0], [48.6, 467.0], [48.7, 467.0], [48.8, 467.0], [48.9, 467.0], [49.0, 468.0], [49.1, 468.0], [49.2, 468.0], [49.3, 468.0], [49.4, 468.0], [49.5, 468.0], [49.6, 468.0], [49.7, 468.0], [49.8, 468.0], [49.9, 468.0], [50.0, 469.0], [50.1, 469.0], [50.2, 469.0], [50.3, 469.0], [50.4, 469.0], [50.5, 469.0], [50.6, 469.0], [50.7, 469.0], [50.8, 469.0], [50.9, 469.0], [51.0, 470.0], [51.1, 470.0], [51.2, 470.0], [51.3, 470.0], [51.4, 470.0], [51.5, 470.0], [51.6, 470.0], [51.7, 470.0], [51.8, 470.0], [51.9, 470.0], [52.0, 471.0], [52.1, 471.0], [52.2, 471.0], [52.3, 471.0], [52.4, 471.0], [52.5, 471.0], [52.6, 471.0], [52.7, 471.0], [52.8, 471.0], [52.9, 471.0], [53.0, 473.0], [53.1, 473.0], [53.2, 473.0], [53.3, 473.0], [53.4, 473.0], [53.5, 473.0], [53.6, 473.0], [53.7, 473.0], [53.8, 473.0], [53.9, 473.0], [54.0, 473.0], [54.1, 473.0], [54.2, 473.0], [54.3, 473.0], [54.4, 473.0], [54.5, 473.0], [54.6, 473.0], [54.7, 473.0], [54.8, 473.0], [54.9, 473.0], [55.0, 474.0], [55.1, 474.0], [55.2, 474.0], [55.3, 474.0], [55.4, 474.0], [55.5, 474.0], [55.6, 474.0], [55.7, 474.0], [55.8, 474.0], [55.9, 474.0], [56.0, 478.0], [56.1, 478.0], [56.2, 478.0], [56.3, 478.0], [56.4, 478.0], [56.5, 478.0], [56.6, 478.0], [56.7, 478.0], [56.8, 478.0], [56.9, 478.0], [57.0, 479.0], [57.1, 479.0], [57.2, 479.0], [57.3, 479.0], [57.4, 479.0], [57.5, 479.0], [57.6, 479.0], [57.7, 479.0], [57.8, 479.0], [57.9, 479.0], [58.0, 480.0], [58.1, 480.0], [58.2, 480.0], [58.3, 480.0], [58.4, 480.0], [58.5, 480.0], [58.6, 480.0], [58.7, 480.0], [58.8, 480.0], [58.9, 480.0], [59.0, 482.0], [59.1, 482.0], [59.2, 482.0], [59.3, 482.0], [59.4, 482.0], [59.5, 482.0], [59.6, 482.0], [59.7, 482.0], [59.8, 482.0], [59.9, 482.0], [60.0, 483.0], [60.1, 483.0], [60.2, 483.0], [60.3, 483.0], [60.4, 483.0], [60.5, 483.0], [60.6, 483.0], [60.7, 483.0], [60.8, 483.0], [60.9, 483.0], [61.0, 484.0], [61.1, 484.0], [61.2, 484.0], [61.3, 484.0], [61.4, 484.0], [61.5, 484.0], [61.6, 484.0], [61.7, 484.0], [61.8, 484.0], [61.9, 484.0], [62.0, 487.0], [62.1, 487.0], [62.2, 487.0], [62.3, 487.0], [62.4, 487.0], [62.5, 487.0], [62.6, 487.0], [62.7, 487.0], [62.8, 487.0], [62.9, 487.0], [63.0, 488.0], [63.1, 488.0], [63.2, 488.0], [63.3, 488.0], [63.4, 488.0], [63.5, 488.0], [63.6, 488.0], [63.7, 488.0], [63.8, 488.0], [63.9, 488.0], [64.0, 496.0], [64.1, 496.0], [64.2, 496.0], [64.3, 496.0], [64.4, 496.0], [64.5, 496.0], [64.6, 496.0], [64.7, 496.0], [64.8, 496.0], [64.9, 496.0], [65.0, 497.0], [65.1, 497.0], [65.2, 497.0], [65.3, 497.0], [65.4, 497.0], [65.5, 497.0], [65.6, 497.0], [65.7, 497.0], [65.8, 497.0], [65.9, 497.0], [66.0, 500.0], [66.1, 500.0], [66.2, 500.0], [66.3, 500.0], [66.4, 500.0], [66.5, 500.0], [66.6, 500.0], [66.7, 500.0], [66.8, 500.0], [66.9, 500.0], [67.0, 501.0], [67.1, 501.0], [67.2, 501.0], [67.3, 501.0], [67.4, 501.0], [67.5, 501.0], [67.6, 501.0], [67.7, 501.0], [67.8, 501.0], [67.9, 501.0], [68.0, 503.0], [68.1, 503.0], [68.2, 503.0], [68.3, 503.0], [68.4, 503.0], [68.5, 503.0], [68.6, 503.0], [68.7, 503.0], [68.8, 503.0], [68.9, 503.0], [69.0, 505.0], [69.1, 505.0], [69.2, 505.0], [69.3, 505.0], [69.4, 505.0], [69.5, 505.0], [69.6, 505.0], [69.7, 505.0], [69.8, 505.0], [69.9, 505.0], [70.0, 505.0], [70.1, 505.0], [70.2, 505.0], [70.3, 505.0], [70.4, 505.0], [70.5, 505.0], [70.6, 505.0], [70.7, 505.0], [70.8, 505.0], [70.9, 505.0], [71.0, 505.0], [71.1, 505.0], [71.2, 505.0], [71.3, 505.0], [71.4, 505.0], [71.5, 505.0], [71.6, 505.0], [71.7, 505.0], [71.8, 505.0], [71.9, 505.0], [72.0, 509.0], [72.1, 509.0], [72.2, 509.0], [72.3, 509.0], [72.4, 509.0], [72.5, 509.0], [72.6, 509.0], [72.7, 509.0], [72.8, 509.0], [72.9, 509.0], [73.0, 511.0], [73.1, 511.0], [73.2, 511.0], [73.3, 511.0], [73.4, 511.0], [73.5, 511.0], [73.6, 511.0], [73.7, 511.0], [73.8, 511.0], [73.9, 511.0], [74.0, 513.0], [74.1, 513.0], [74.2, 513.0], [74.3, 513.0], [74.4, 513.0], [74.5, 513.0], [74.6, 513.0], [74.7, 513.0], [74.8, 513.0], [74.9, 513.0], [75.0, 518.0], [75.1, 518.0], [75.2, 518.0], [75.3, 518.0], [75.4, 518.0], [75.5, 518.0], [75.6, 518.0], [75.7, 518.0], [75.8, 518.0], [75.9, 518.0], [76.0, 520.0], [76.1, 520.0], [76.2, 520.0], [76.3, 520.0], [76.4, 520.0], [76.5, 520.0], [76.6, 520.0], [76.7, 520.0], [76.8, 520.0], [76.9, 520.0], [77.0, 520.0], [77.1, 520.0], [77.2, 520.0], [77.3, 520.0], [77.4, 520.0], [77.5, 520.0], [77.6, 520.0], [77.7, 520.0], [77.8, 520.0], [77.9, 520.0], [78.0, 527.0], [78.1, 527.0], [78.2, 527.0], [78.3, 527.0], [78.4, 527.0], [78.5, 527.0], [78.6, 527.0], [78.7, 527.0], [78.8, 527.0], [78.9, 527.0], [79.0, 528.0], [79.1, 528.0], [79.2, 528.0], [79.3, 528.0], [79.4, 528.0], [79.5, 528.0], [79.6, 528.0], [79.7, 528.0], [79.8, 528.0], [79.9, 528.0], [80.0, 528.0], [80.1, 528.0], [80.2, 528.0], [80.3, 528.0], [80.4, 528.0], [80.5, 528.0], [80.6, 528.0], [80.7, 528.0], [80.8, 528.0], [80.9, 528.0], [81.0, 532.0], [81.1, 532.0], [81.2, 532.0], [81.3, 532.0], [81.4, 532.0], [81.5, 532.0], [81.6, 532.0], [81.7, 532.0], [81.8, 532.0], [81.9, 532.0], [82.0, 539.0], [82.1, 539.0], [82.2, 539.0], [82.3, 539.0], [82.4, 539.0], [82.5, 539.0], [82.6, 539.0], [82.7, 539.0], [82.8, 539.0], [82.9, 539.0], [83.0, 541.0], [83.1, 541.0], [83.2, 541.0], [83.3, 541.0], [83.4, 541.0], [83.5, 541.0], [83.6, 541.0], [83.7, 541.0], [83.8, 541.0], [83.9, 541.0], [84.0, 554.0], [84.1, 554.0], [84.2, 554.0], [84.3, 554.0], [84.4, 554.0], [84.5, 554.0], [84.6, 554.0], [84.7, 554.0], [84.8, 554.0], [84.9, 554.0], [85.0, 555.0], [85.1, 555.0], [85.2, 555.0], [85.3, 555.0], [85.4, 555.0], [85.5, 555.0], [85.6, 555.0], [85.7, 555.0], [85.8, 555.0], [85.9, 555.0], [86.0, 555.0], [86.1, 555.0], [86.2, 555.0], [86.3, 555.0], [86.4, 555.0], [86.5, 555.0], [86.6, 555.0], [86.7, 555.0], [86.8, 555.0], [86.9, 555.0], [87.0, 567.0], [87.1, 567.0], [87.2, 567.0], [87.3, 567.0], [87.4, 567.0], [87.5, 567.0], [87.6, 567.0], [87.7, 567.0], [87.8, 567.0], [87.9, 567.0], [88.0, 568.0], [88.1, 568.0], [88.2, 568.0], [88.3, 568.0], [88.4, 568.0], [88.5, 568.0], [88.6, 568.0], [88.7, 568.0], [88.8, 568.0], [88.9, 568.0], [89.0, 572.0], [89.1, 572.0], [89.2, 572.0], [89.3, 572.0], [89.4, 572.0], [89.5, 572.0], [89.6, 572.0], [89.7, 572.0], [89.8, 572.0], [89.9, 572.0], [90.0, 577.0], [90.1, 577.0], [90.2, 577.0], [90.3, 577.0], [90.4, 577.0], [90.5, 577.0], [90.6, 577.0], [90.7, 577.0], [90.8, 577.0], [90.9, 577.0], [91.0, 578.0], [91.1, 578.0], [91.2, 578.0], [91.3, 578.0], [91.4, 578.0], [91.5, 578.0], [91.6, 578.0], [91.7, 578.0], [91.8, 578.0], [91.9, 578.0], [92.0, 580.0], [92.1, 580.0], [92.2, 580.0], [92.3, 580.0], [92.4, 580.0], [92.5, 580.0], [92.6, 580.0], [92.7, 580.0], [92.8, 580.0], [92.9, 580.0], [93.0, 584.0], [93.1, 584.0], [93.2, 584.0], [93.3, 584.0], [93.4, 584.0], [93.5, 584.0], [93.6, 584.0], [93.7, 584.0], [93.8, 584.0], [93.9, 584.0], [94.0, 596.0], [94.1, 596.0], [94.2, 596.0], [94.3, 596.0], [94.4, 596.0], [94.5, 596.0], [94.6, 596.0], [94.7, 596.0], [94.8, 596.0], [94.9, 596.0], [95.0, 596.0], [95.1, 596.0], [95.2, 596.0], [95.3, 596.0], [95.4, 596.0], [95.5, 596.0], [95.6, 596.0], [95.7, 596.0], [95.8, 596.0], [95.9, 596.0], [96.0, 611.0], [96.1, 611.0], [96.2, 611.0], [96.3, 611.0], [96.4, 611.0], [96.5, 611.0], [96.6, 611.0], [96.7, 611.0], [96.8, 611.0], [96.9, 611.0], [97.0, 615.0], [97.1, 615.0], [97.2, 615.0], [97.3, 615.0], [97.4, 615.0], [97.5, 615.0], [97.6, 615.0], [97.7, 615.0], [97.8, 615.0], [97.9, 615.0], [98.0, 642.0], [98.1, 642.0], [98.2, 642.0], [98.3, 642.0], [98.4, 642.0], [98.5, 642.0], [98.6, 642.0], [98.7, 642.0], [98.8, 642.0], [98.9, 642.0], [99.0, 655.0], [99.1, 655.0], [99.2, 655.0], [99.3, 655.0], [99.4, 655.0], [99.5, 655.0], [99.6, 655.0], [99.7, 655.0], [99.8, 655.0], [99.9, 655.0]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "maxX": 100.0, "title": "Response Time Percentiles"}},
        getOptions: function() {
            return {
                series: {
                    points: { show: false }
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: '#legendResponseTimePercentiles'
                },
                xaxis: {
                    tickDecimals: 1,
                    axisLabel: "Percentiles",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Percentile value in ms",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s : %x.2 percentile was %y ms"
                },
                selection: { mode: "xy" },
            };
        },
        createGraph: function() {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesResponseTimePercentiles"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotResponseTimesPercentiles"), dataset, options);
            // setup overview
            $.plot($("#overviewResponseTimesPercentiles"), dataset, prepareOverviewOptions(options));
        }
};

/**
 * @param elementId Id of element where we display message
 */
function setEmptyGraph(elementId) {
    $(function() {
        $(elementId).text("No graph series with filter="+seriesFilter);
    });
}

// Response times percentiles
function refreshResponseTimePercentiles() {
    var infos = responseTimePercentilesInfos;
    prepareSeries(infos.data);
    if(infos.data.result.series.length == 0) {
        setEmptyGraph("#bodyResponseTimePercentiles");
        return;
    }
    if (isGraph($("#flotResponseTimesPercentiles"))){
        infos.createGraph();
    } else {
        var choiceContainer = $("#choicesResponseTimePercentiles");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotResponseTimesPercentiles", "#overviewResponseTimesPercentiles");
        $('#bodyResponseTimePercentiles .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
}

var responseTimeDistributionInfos = {
        data: {"result": {"minY": 4.0, "minX": 300.0, "maxY": 59.0, "series": [{"data": [[300.0, 7.0], [600.0, 4.0], [400.0, 59.0], [500.0, 30.0]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 100, "maxX": 600.0, "title": "Response Time Distribution"}},
        getOptions: function() {
            var granularity = this.data.result.granularity;
            return {
                legend: {
                    noColumns: 2,
                    show: true,
                    container: '#legendResponseTimeDistribution'
                },
                xaxis:{
                    axisLabel: "Response times in ms",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Number of responses",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                bars : {
                    show: true,
                    barWidth: this.data.result.granularity
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: function(label, xval, yval, flotItem){
                        return yval + " responses for " + label + " were between " + xval + " and " + (xval + granularity) + " ms";
                    }
                }
            };
        },
        createGraph: function() {
            var data = this.data;
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotResponseTimeDistribution"), prepareData(data.result.series, $("#choicesResponseTimeDistribution")), options);
        }

};

// Response time distribution
function refreshResponseTimeDistribution() {
    var infos = responseTimeDistributionInfos;
    prepareSeries(infos.data);
    if(infos.data.result.series.length == 0) {
        setEmptyGraph("#bodyResponseTimeDistribution");
        return;
    }
    if (isGraph($("#flotResponseTimeDistribution"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesResponseTimeDistribution");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        $('#footerResponseTimeDistribution .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};


var syntheticResponseTimeDistributionInfos = {
        data: {"result": {"minY": 1.0, "minX": 0.0, "ticks": [[0, "Requests having \nresponse time <= 500ms"], [1, "Requests having \nresponse time > 500ms and <= 1,500ms"], [2, "Requests having \nresponse time > 1,500ms"], [3, "Requests in error"]], "maxY": 66.0, "series": [{"data": [[0.0, 66.0]], "color": "#9ACD32", "isOverall": false, "label": "Requests having \nresponse time <= 500ms", "isController": false}, {"data": [[1.0, 33.0]], "color": "yellow", "isOverall": false, "label": "Requests having \nresponse time > 500ms and <= 1,500ms", "isController": false}, {"data": [], "color": "orange", "isOverall": false, "label": "Requests having \nresponse time > 1,500ms", "isController": false}, {"data": [[3.0, 1.0]], "color": "#FF6347", "isOverall": false, "label": "Requests in error", "isController": false}], "supportsControllersDiscrimination": false, "maxX": 3.0, "title": "Synthetic Response Times Distribution"}},
        getOptions: function() {
            return {
                legend: {
                    noColumns: 2,
                    show: true,
                    container: '#legendSyntheticResponseTimeDistribution'
                },
                xaxis:{
                    axisLabel: "Response times ranges",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                    tickLength:0,
                    min:-0.5,
                    max:3.5
                },
                yaxis: {
                    axisLabel: "Number of responses",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                bars : {
                    show: true,
                    align: "center",
                    barWidth: 0.25,
                    fill:.75
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: function(label, xval, yval, flotItem){
                        return yval + " " + label;
                    }
                }
            };
        },
        createGraph: function() {
            var data = this.data;
            var options = this.getOptions();
            prepareOptions(options, data);
            options.xaxis.ticks = data.result.ticks;
            $.plot($("#flotSyntheticResponseTimeDistribution"), prepareData(data.result.series, $("#choicesSyntheticResponseTimeDistribution")), options);
        }

};

// Response time distribution
function refreshSyntheticResponseTimeDistribution() {
    var infos = syntheticResponseTimeDistributionInfos;
    prepareSeries(infos.data, true);
    if (isGraph($("#flotSyntheticResponseTimeDistribution"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesSyntheticResponseTimeDistribution");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        $('#footerSyntheticResponseTimeDistribution .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

var activeThreadsOverTimeInfos = {
        data: {"result": {"minY": 9.529999999999998, "minX": 1.79103408E12, "maxY": 9.529999999999998, "series": [{"data": [[1.79103408E12, 9.529999999999998]], "isOverall": false, "label": "Authenticated users", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103408E12, "title": "Active Threads Over Time"}},
        getOptions: function() {
            return {
                series: {
                    stack: true,
                    lines: {
                        show: true,
                        fill: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Number of active threads",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20
                },
                legend: {
                    noColumns: 6,
                    show: true,
                    container: '#legendActiveThreadsOverTime'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                selection: {
                    mode: 'xy'
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s : At %x there were %y active threads"
                }
            };
        },
        createGraph: function() {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesActiveThreadsOverTime"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotActiveThreadsOverTime"), dataset, options);
            // setup overview
            $.plot($("#overviewActiveThreadsOverTime"), dataset, prepareOverviewOptions(options));
        }
};

// Active Threads Over Time
function refreshActiveThreadsOverTime(fixTimestamps) {
    var infos = activeThreadsOverTimeInfos;
    prepareSeries(infos.data);
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotActiveThreadsOverTime"))) {
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesActiveThreadsOverTime");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotActiveThreadsOverTime", "#overviewActiveThreadsOverTime");
        $('#footerActiveThreadsOverTime .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

var timeVsThreadsInfos = {
        data: {"result": {"minY": 353.0, "minX": 1.0, "maxY": 494.5, "series": [{"data": [[8.0, 418.0], [4.0, 390.0], [2.0, 355.0], [1.0, 353.0], [10.0, 485.83516483516473], [5.0, 390.0], [6.0, 404.0], [3.0, 434.0], [7.0, 494.5]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}, {"data": [[9.529999999999998, 479.43999999999994]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-Aggregated", "isController": false}], "supportsControllersDiscrimination": true, "maxX": 10.0, "title": "Time VS Threads"}},
        getOptions: function() {
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    axisLabel: "Number of active threads",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Average response times in ms",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20
                },
                legend: { noColumns: 2,show: true, container: '#legendTimeVsThreads' },
                selection: {
                    mode: 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s: At %x.2 active threads, Average response time was %y.2 ms"
                }
            };
        },
        createGraph: function() {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesTimeVsThreads"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotTimesVsThreads"), dataset, options);
            // setup overview
            $.plot($("#overviewTimesVsThreads"), dataset, prepareOverviewOptions(options));
        }
};

// Time vs threads
function refreshTimeVsThreads(){
    var infos = timeVsThreadsInfos;
    prepareSeries(infos.data);
    if(infos.data.result.series.length == 0) {
        setEmptyGraph("#bodyTimeVsThreads");
        return;
    }
    if(isGraph($("#flotTimesVsThreads"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesTimeVsThreads");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotTimesVsThreads", "#overviewTimesVsThreads");
        $('#footerTimeVsThreads .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

var bytesThroughputOverTimeInfos = {
        data : {"result": {"minY": 1440.0, "minX": 1.79103408E12, "maxY": 497154.36666666664, "series": [{"data": [[1.79103408E12, 497154.36666666664]], "isOverall": false, "label": "Bytes received per second", "isController": false}, {"data": [[1.79103408E12, 1440.0]], "isOverall": false, "label": "Bytes sent per second", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103408E12, "title": "Bytes Throughput Over Time"}},
        getOptions : function(){
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity) ,
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Bytes / sec",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: '#legendBytesThroughputOverTime'
                },
                selection: {
                    mode: "xy"
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s at %x was %y"
                }
            };
        },
        createGraph : function() {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesBytesThroughputOverTime"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotBytesThroughputOverTime"), dataset, options);
            // setup overview
            $.plot($("#overviewBytesThroughputOverTime"), dataset, prepareOverviewOptions(options));
        }
};

// Bytes throughput Over Time
function refreshBytesThroughputOverTime(fixTimestamps) {
    var infos = bytesThroughputOverTimeInfos;
    prepareSeries(infos.data);
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotBytesThroughputOverTime"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesBytesThroughputOverTime");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotBytesThroughputOverTime", "#overviewBytesThroughputOverTime");
        $('#footerBytesThroughputOverTime .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
}

var responseTimesOverTimeInfos = {
        data: {"result": {"minY": 479.43999999999994, "minX": 1.79103408E12, "maxY": 479.43999999999994, "series": [{"data": [[1.79103408E12, 479.43999999999994]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103408E12, "title": "Response Time Over Time"}},
        getOptions: function(){
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Average response time in ms",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: '#legendResponseTimesOverTime'
                },
                selection: {
                    mode: 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s : at %x Average response time was %y ms"
                }
            };
        },
        createGraph: function() {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesResponseTimesOverTime"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotResponseTimesOverTime"), dataset, options);
            // setup overview
            $.plot($("#overviewResponseTimesOverTime"), dataset, prepareOverviewOptions(options));
        }
};

// Response Times Over Time
function refreshResponseTimeOverTime(fixTimestamps) {
    var infos = responseTimesOverTimeInfos;
    prepareSeries(infos.data);
    if(infos.data.result.series.length == 0) {
        setEmptyGraph("#bodyResponseTimeOverTime");
        return;
    }
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotResponseTimesOverTime"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesResponseTimesOverTime");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotResponseTimesOverTime", "#overviewResponseTimesOverTime");
        $('#footerResponseTimesOverTime .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

var latenciesOverTimeInfos = {
        data: {"result": {"minY": 476.39000000000004, "minX": 1.79103408E12, "maxY": 476.39000000000004, "series": [{"data": [[1.79103408E12, 476.39000000000004]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103408E12, "title": "Latencies Over Time"}},
        getOptions: function() {
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Average response latencies in ms",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: '#legendLatenciesOverTime'
                },
                selection: {
                    mode: 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s : at %x Average latency was %y ms"
                }
            };
        },
        createGraph: function () {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesLatenciesOverTime"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotLatenciesOverTime"), dataset, options);
            // setup overview
            $.plot($("#overviewLatenciesOverTime"), dataset, prepareOverviewOptions(options));
        }
};

// Latencies Over Time
function refreshLatenciesOverTime(fixTimestamps) {
    var infos = latenciesOverTimeInfos;
    prepareSeries(infos.data);
    if(infos.data.result.series.length == 0) {
        setEmptyGraph("#bodyLatenciesOverTime");
        return;
    }
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotLatenciesOverTime"))) {
        infos.createGraph();
    }else {
        var choiceContainer = $("#choicesLatenciesOverTime");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotLatenciesOverTime", "#overviewLatenciesOverTime");
        $('#footerLatenciesOverTime .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

var connectTimeOverTimeInfos = {
        data: {"result": {"minY": 0.010000000000000002, "minX": 1.79103408E12, "maxY": 0.010000000000000002, "series": [{"data": [[1.79103408E12, 0.010000000000000002]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103408E12, "title": "Connect Time Over Time"}},
        getOptions: function() {
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getConnectTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Average Connect Time in ms",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: '#legendConnectTimeOverTime'
                },
                selection: {
                    mode: 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s : at %x Average connect time was %y ms"
                }
            };
        },
        createGraph: function () {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesConnectTimeOverTime"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotConnectTimeOverTime"), dataset, options);
            // setup overview
            $.plot($("#overviewConnectTimeOverTime"), dataset, prepareOverviewOptions(options));
        }
};

// Connect Time Over Time
function refreshConnectTimeOverTime(fixTimestamps) {
    var infos = connectTimeOverTimeInfos;
    prepareSeries(infos.data);
    if(infos.data.result.series.length == 0) {
        setEmptyGraph("#bodyConnectTimeOverTime");
        return;
    }
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotConnectTimeOverTime"))) {
        infos.createGraph();
    }else {
        var choiceContainer = $("#choicesConnectTimeOverTime");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotConnectTimeOverTime", "#overviewConnectTimeOverTime");
        $('#footerConnectTimeOverTime .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

var responseTimePercentilesOverTimeInfos = {
        data: {"result": {"minY": 353.0, "minX": 1.79103408E12, "maxY": 655.0, "series": [{"data": [[1.79103408E12, 655.0]], "isOverall": false, "label": "Max", "isController": false}, {"data": [[1.79103408E12, 353.0]], "isOverall": false, "label": "Min", "isController": false}, {"data": [[1.79103408E12, 577.0]], "isOverall": false, "label": "90th percentile", "isController": false}, {"data": [[1.79103408E12, 655.0]], "isOverall": false, "label": "99th percentile", "isController": false}, {"data": [[1.79103408E12, 469.0]], "isOverall": false, "label": "Median", "isController": false}, {"data": [[1.79103408E12, 596.0]], "isOverall": false, "label": "95th percentile", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103408E12, "title": "Response Time Percentiles Over Time (successful requests only)"}},
        getOptions: function() {
            return {
                series: {
                    lines: {
                        show: true,
                        fill: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Response Time in ms",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: '#legendResponseTimePercentilesOverTime'
                },
                selection: {
                    mode: 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s : at %x Response time was %y ms"
                }
            };
        },
        createGraph: function () {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesResponseTimePercentilesOverTime"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotResponseTimePercentilesOverTime"), dataset, options);
            // setup overview
            $.plot($("#overviewResponseTimePercentilesOverTime"), dataset, prepareOverviewOptions(options));
        }
};

// Response Time Percentiles Over Time
function refreshResponseTimePercentilesOverTime(fixTimestamps) {
    var infos = responseTimePercentilesOverTimeInfos;
    prepareSeries(infos.data);
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotResponseTimePercentilesOverTime"))) {
        infos.createGraph();
    }else {
        var choiceContainer = $("#choicesResponseTimePercentilesOverTime");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotResponseTimePercentilesOverTime", "#overviewResponseTimePercentilesOverTime");
        $('#footerResponseTimePercentilesOverTime .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};


var responseTimeVsRequestInfos = {
    data: {"result": {"minY": 308.0, "minX": 1.0, "maxY": 488.5, "series": [{"data": [[1.0, 353.0], [18.0, 488.5], [19.0, 463.5], [21.0, 483.5], [22.0, 454.0]], "isOverall": false, "label": "Successes", "isController": false}, {"data": [[21.0, 308.0]], "isOverall": false, "label": "Failures", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 1000, "maxX": 22.0, "title": "Response Time Vs Request"}},
    getOptions: function() {
        return {
            series: {
                lines: {
                    show: false
                },
                points: {
                    show: true
                }
            },
            xaxis: {
                axisLabel: "Global number of requests per second",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 20,
            },
            yaxis: {
                axisLabel: "Median Response Time in ms",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 20,
            },
            legend: {
                noColumns: 2,
                show: true,
                container: '#legendResponseTimeVsRequest'
            },
            selection: {
                mode: 'xy'
            },
            grid: {
                hoverable: true // IMPORTANT! this is needed for tooltip to work
            },
            tooltip: true,
            tooltipOpts: {
                content: "%s : Median response time at %x req/s was %y ms"
            },
            colors: ["#9ACD32", "#FF6347"]
        };
    },
    createGraph: function () {
        var data = this.data;
        var dataset = prepareData(data.result.series, $("#choicesResponseTimeVsRequest"));
        var options = this.getOptions();
        prepareOptions(options, data);
        $.plot($("#flotResponseTimeVsRequest"), dataset, options);
        // setup overview
        $.plot($("#overviewResponseTimeVsRequest"), dataset, prepareOverviewOptions(options));

    }
};

// Response Time vs Request
function refreshResponseTimeVsRequest() {
    var infos = responseTimeVsRequestInfos;
    prepareSeries(infos.data);
    if (isGraph($("#flotResponseTimeVsRequest"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesResponseTimeVsRequest");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotResponseTimeVsRequest", "#overviewResponseTimeVsRequest");
        $('#footerResponseRimeVsRequest .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};


var latenciesVsRequestInfos = {
    data: {"result": {"minY": 303.0, "minX": 1.0, "maxY": 485.5, "series": [{"data": [[1.0, 351.0], [18.0, 485.5], [19.0, 460.0], [21.0, 481.0], [22.0, 451.0]], "isOverall": false, "label": "Successes", "isController": false}, {"data": [[21.0, 303.0]], "isOverall": false, "label": "Failures", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 1000, "maxX": 22.0, "title": "Latencies Vs Request"}},
    getOptions: function() {
        return{
            series: {
                lines: {
                    show: false
                },
                points: {
                    show: true
                }
            },
            xaxis: {
                axisLabel: "Global number of requests per second",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 20,
            },
            yaxis: {
                axisLabel: "Median Latency in ms",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 20,
            },
            legend: { noColumns: 2,show: true, container: '#legendLatencyVsRequest' },
            selection: {
                mode: 'xy'
            },
            grid: {
                hoverable: true // IMPORTANT! this is needed for tooltip to work
            },
            tooltip: true,
            tooltipOpts: {
                content: "%s : Median Latency time at %x req/s was %y ms"
            },
            colors: ["#9ACD32", "#FF6347"]
        };
    },
    createGraph: function () {
        var data = this.data;
        var dataset = prepareData(data.result.series, $("#choicesLatencyVsRequest"));
        var options = this.getOptions();
        prepareOptions(options, data);
        $.plot($("#flotLatenciesVsRequest"), dataset, options);
        // setup overview
        $.plot($("#overviewLatenciesVsRequest"), dataset, prepareOverviewOptions(options));
    }
};

// Latencies vs Request
function refreshLatenciesVsRequest() {
        var infos = latenciesVsRequestInfos;
        prepareSeries(infos.data);
        if(isGraph($("#flotLatenciesVsRequest"))){
            infos.createGraph();
        }else{
            var choiceContainer = $("#choicesLatencyVsRequest");
            createLegend(choiceContainer, infos);
            infos.createGraph();
            setGraphZoomable("#flotLatenciesVsRequest", "#overviewLatenciesVsRequest");
            $('#footerLatenciesVsRequest .legendColorBox > div').each(function(i){
                $(this).clone().prependTo(choiceContainer.find("li").eq(i));
            });
        }
};

var hitsPerSecondInfos = {
        data: {"result": {"minY": 1.6666666666666667, "minX": 1.79103408E12, "maxY": 1.6666666666666667, "series": [{"data": [[1.79103408E12, 1.6666666666666667]], "isOverall": false, "label": "hitsPerSecond", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103408E12, "title": "Hits Per Second"}},
        getOptions: function() {
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Number of hits / sec",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: "#legendHitsPerSecond"
                },
                selection: {
                    mode : 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s at %x was %y.2 hits/sec"
                }
            };
        },
        createGraph: function createGraph() {
            var data = this.data;
            var dataset = prepareData(data.result.series, $("#choicesHitsPerSecond"));
            var options = this.getOptions();
            prepareOptions(options, data);
            $.plot($("#flotHitsPerSecond"), dataset, options);
            // setup overview
            $.plot($("#overviewHitsPerSecond"), dataset, prepareOverviewOptions(options));
        }
};

// Hits per second
function refreshHitsPerSecond(fixTimestamps) {
    var infos = hitsPerSecondInfos;
    prepareSeries(infos.data);
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if (isGraph($("#flotHitsPerSecond"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesHitsPerSecond");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotHitsPerSecond", "#overviewHitsPerSecond");
        $('#footerHitsPerSecond .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
}

var codesPerSecondInfos = {
        data: {"result": {"minY": 0.016666666666666666, "minX": 1.79103408E12, "maxY": 1.65, "series": [{"data": [[1.79103408E12, 1.65]], "isOverall": false, "label": "200", "isController": false}, {"data": [[1.79103408E12, 0.016666666666666666]], "isOverall": false, "label": "500", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103408E12, "title": "Codes Per Second"}},
        getOptions: function(){
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Number of responses / sec",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: "#legendCodesPerSecond"
                },
                selection: {
                    mode: 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "Number of Response Codes %s at %x was %y.2 responses / sec"
                }
            };
        },
    createGraph: function() {
        var data = this.data;
        var dataset = prepareData(data.result.series, $("#choicesCodesPerSecond"));
        var options = this.getOptions();
        prepareOptions(options, data);
        $.plot($("#flotCodesPerSecond"), dataset, options);
        // setup overview
        $.plot($("#overviewCodesPerSecond"), dataset, prepareOverviewOptions(options));
    }
};

// Codes per second
function refreshCodesPerSecond(fixTimestamps) {
    var infos = codesPerSecondInfos;
    prepareSeries(infos.data);
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotCodesPerSecond"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesCodesPerSecond");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotCodesPerSecond", "#overviewCodesPerSecond");
        $('#footerCodesPerSecond .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

var transactionsPerSecondInfos = {
        data: {"result": {"minY": 0.016666666666666666, "minX": 1.79103408E12, "maxY": 1.65, "series": [{"data": [[1.79103408E12, 1.65]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-success", "isController": false}, {"data": [[1.79103408E12, 0.016666666666666666]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-failure", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103408E12, "title": "Transactions Per Second"}},
        getOptions: function(){
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Number of transactions / sec",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: "#legendTransactionsPerSecond"
                },
                selection: {
                    mode: 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s at %x was %y transactions / sec"
                }
            };
        },
    createGraph: function () {
        var data = this.data;
        var dataset = prepareData(data.result.series, $("#choicesTransactionsPerSecond"));
        var options = this.getOptions();
        prepareOptions(options, data);
        $.plot($("#flotTransactionsPerSecond"), dataset, options);
        // setup overview
        $.plot($("#overviewTransactionsPerSecond"), dataset, prepareOverviewOptions(options));
    }
};

// Transactions per second
function refreshTransactionsPerSecond(fixTimestamps) {
    var infos = transactionsPerSecondInfos;
    prepareSeries(infos.data);
    if(infos.data.result.series.length == 0) {
        setEmptyGraph("#bodyTransactionsPerSecond");
        return;
    }
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotTransactionsPerSecond"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesTransactionsPerSecond");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotTransactionsPerSecond", "#overviewTransactionsPerSecond");
        $('#footerTransactionsPerSecond .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

var totalTPSInfos = {
        data: {"result": {"minY": 0.016666666666666666, "minX": 1.79103408E12, "maxY": 1.65, "series": [{"data": [[1.79103408E12, 1.65]], "isOverall": false, "label": "Transaction-success", "isController": false}, {"data": [[1.79103408E12, 0.016666666666666666]], "isOverall": false, "label": "Transaction-failure", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103408E12, "title": "Total Transactions Per Second"}},
        getOptions: function(){
            return {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    mode: "time",
                    timeformat: getTimeFormat(this.data.result.granularity),
                    axisLabel: getElapsedTimeLabel(this.data.result.granularity),
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20,
                },
                yaxis: {
                    axisLabel: "Number of transactions / sec",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 20
                },
                legend: {
                    noColumns: 2,
                    show: true,
                    container: "#legendTotalTPS"
                },
                selection: {
                    mode: 'xy'
                },
                grid: {
                    hoverable: true // IMPORTANT! this is needed for tooltip to
                                    // work
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s at %x was %y transactions / sec"
                },
                colors: ["#9ACD32", "#FF6347"]
            };
        },
    createGraph: function () {
        var data = this.data;
        var dataset = prepareData(data.result.series, $("#choicesTotalTPS"));
        var options = this.getOptions();
        prepareOptions(options, data);
        $.plot($("#flotTotalTPS"), dataset, options);
        // setup overview
        $.plot($("#overviewTotalTPS"), dataset, prepareOverviewOptions(options));
    }
};

// Total Transactions per second
function refreshTotalTPS(fixTimestamps) {
    var infos = totalTPSInfos;
    // We want to ignore seriesFilter
    prepareSeries(infos.data, false, true);
    if(fixTimestamps) {
        fixTimeStamps(infos.data.result.series, 28800000);
    }
    if(isGraph($("#flotTotalTPS"))){
        infos.createGraph();
    }else{
        var choiceContainer = $("#choicesTotalTPS");
        createLegend(choiceContainer, infos);
        infos.createGraph();
        setGraphZoomable("#flotTotalTPS", "#overviewTotalTPS");
        $('#footerTotalTPS .legendColorBox > div').each(function(i){
            $(this).clone().prependTo(choiceContainer.find("li").eq(i));
        });
    }
};

// Collapse the graph matching the specified DOM element depending the collapsed
// status
function collapse(elem, collapsed){
    if(collapsed){
        $(elem).parent().find(".fa-chevron-up").removeClass("fa-chevron-up").addClass("fa-chevron-down");
    } else {
        $(elem).parent().find(".fa-chevron-down").removeClass("fa-chevron-down").addClass("fa-chevron-up");
        if (elem.id == "bodyBytesThroughputOverTime") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshBytesThroughputOverTime(true);
            }
            document.location.href="#bytesThroughputOverTime";
        } else if (elem.id == "bodyLatenciesOverTime") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshLatenciesOverTime(true);
            }
            document.location.href="#latenciesOverTime";
        } else if (elem.id == "bodyCustomGraph") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshCustomGraph(true);
            }
            document.location.href="#responseCustomGraph";
        } else if (elem.id == "bodyConnectTimeOverTime") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshConnectTimeOverTime(true);
            }
            document.location.href="#connectTimeOverTime";
        } else if (elem.id == "bodyResponseTimePercentilesOverTime") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshResponseTimePercentilesOverTime(true);
            }
            document.location.href="#responseTimePercentilesOverTime";
        } else if (elem.id == "bodyResponseTimeDistribution") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshResponseTimeDistribution();
            }
            document.location.href="#responseTimeDistribution" ;
        } else if (elem.id == "bodySyntheticResponseTimeDistribution") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshSyntheticResponseTimeDistribution();
            }
            document.location.href="#syntheticResponseTimeDistribution" ;
        } else if (elem.id == "bodyActiveThreadsOverTime") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshActiveThreadsOverTime(true);
            }
            document.location.href="#activeThreadsOverTime";
        } else if (elem.id == "bodyTimeVsThreads") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshTimeVsThreads();
            }
            document.location.href="#timeVsThreads" ;
        } else if (elem.id == "bodyCodesPerSecond") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshCodesPerSecond(true);
            }
            document.location.href="#codesPerSecond";
        } else if (elem.id == "bodyTransactionsPerSecond") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshTransactionsPerSecond(true);
            }
            document.location.href="#transactionsPerSecond";
        } else if (elem.id == "bodyTotalTPS") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshTotalTPS(true);
            }
            document.location.href="#totalTPS";
        } else if (elem.id == "bodyResponseTimeVsRequest") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshResponseTimeVsRequest();
            }
            document.location.href="#responseTimeVsRequest";
        } else if (elem.id == "bodyLatenciesVsRequest") {
            if (isGraph($(elem).find('.flot-chart-content')) == false) {
                refreshLatenciesVsRequest();
            }
            document.location.href="#latencyVsRequest";
        }
    }
}

/*
 * Activates or deactivates all series of the specified graph (represented by id parameter)
 * depending on checked argument.
 */
function toggleAll(id, checked){
    var placeholder = document.getElementById(id);

    var cases = $(placeholder).find(':checkbox');
    cases.prop('checked', checked);
    $(cases).parent().children().children().toggleClass("legend-disabled", !checked);

    var choiceContainer;
    if ( id == "choicesBytesThroughputOverTime"){
        choiceContainer = $("#choicesBytesThroughputOverTime");
        refreshBytesThroughputOverTime(false);
    } else if(id == "choicesResponseTimesOverTime"){
        choiceContainer = $("#choicesResponseTimesOverTime");
        refreshResponseTimeOverTime(false);
    }else if(id == "choicesResponseCustomGraph"){
        choiceContainer = $("#choicesResponseCustomGraph");
        refreshCustomGraph(false);
    } else if ( id == "choicesLatenciesOverTime"){
        choiceContainer = $("#choicesLatenciesOverTime");
        refreshLatenciesOverTime(false);
    } else if ( id == "choicesConnectTimeOverTime"){
        choiceContainer = $("#choicesConnectTimeOverTime");
        refreshConnectTimeOverTime(false);
    } else if ( id == "choicesResponseTimePercentilesOverTime"){
        choiceContainer = $("#choicesResponseTimePercentilesOverTime");
        refreshResponseTimePercentilesOverTime(false);
    } else if ( id == "choicesResponseTimePercentiles"){
        choiceContainer = $("#choicesResponseTimePercentiles");
        refreshResponseTimePercentiles();
    } else if(id == "choicesActiveThreadsOverTime"){
        choiceContainer = $("#choicesActiveThreadsOverTime");
        refreshActiveThreadsOverTime(false);
    } else if ( id == "choicesTimeVsThreads"){
        choiceContainer = $("#choicesTimeVsThreads");
        refreshTimeVsThreads();
    } else if ( id == "choicesSyntheticResponseTimeDistribution"){
        choiceContainer = $("#choicesSyntheticResponseTimeDistribution");
        refreshSyntheticResponseTimeDistribution();
    } else if ( id == "choicesResponseTimeDistribution"){
        choiceContainer = $("#choicesResponseTimeDistribution");
        refreshResponseTimeDistribution();
    } else if ( id == "choicesHitsPerSecond"){
        choiceContainer = $("#choicesHitsPerSecond");
        refreshHitsPerSecond(false);
    } else if(id == "choicesCodesPerSecond"){
        choiceContainer = $("#choicesCodesPerSecond");
        refreshCodesPerSecond(false);
    } else if ( id == "choicesTransactionsPerSecond"){
        choiceContainer = $("#choicesTransactionsPerSecond");
        refreshTransactionsPerSecond(false);
    } else if ( id == "choicesTotalTPS"){
        choiceContainer = $("#choicesTotalTPS");
        refreshTotalTPS(false);
    } else if ( id == "choicesResponseTimeVsRequest"){
        choiceContainer = $("#choicesResponseTimeVsRequest");
        refreshResponseTimeVsRequest();
    } else if ( id == "choicesLatencyVsRequest"){
        choiceContainer = $("#choicesLatencyVsRequest");
        refreshLatenciesVsRequest();
    }
    var color = checked ? "black" : "#818181";
    if(choiceContainer != null) {
        choiceContainer.find("label").each(function(){
            this.style.color = color;
        });
    }
}

