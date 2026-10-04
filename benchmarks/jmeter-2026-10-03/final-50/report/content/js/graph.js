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
        data: {"result": {"minY": 309.0, "minX": 0.0, "maxY": 12743.0, "series": [{"data": [[0.0, 309.0], [0.1, 309.0], [0.2, 343.0], [0.3, 343.0], [0.4, 361.0], [0.5, 361.0], [0.6, 361.0], [0.7, 403.0], [0.8, 522.0], [0.9, 522.0], [1.0, 525.0], [1.1, 525.0], [1.2, 525.0], [1.3, 525.0], [1.4, 712.0], [1.5, 712.0], [1.6, 794.0], [1.7, 794.0], [1.8, 799.0], [1.9, 799.0], [2.0, 844.0], [2.1, 844.0], [2.2, 904.0], [2.3, 904.0], [2.4, 951.0], [2.5, 951.0], [2.6, 955.0], [2.7, 955.0], [2.8, 955.0], [2.9, 961.0], [3.0, 961.0], [3.1, 1023.0], [3.2, 1023.0], [3.3, 1024.0], [3.4, 1024.0], [3.5, 1045.0], [3.6, 1045.0], [3.7, 1141.0], [3.8, 1141.0], [3.9, 1182.0], [4.0, 1182.0], [4.1, 1237.0], [4.2, 1237.0], [4.3, 1252.0], [4.4, 1252.0], [4.5, 1324.0], [4.6, 1324.0], [4.7, 1380.0], [4.8, 1380.0], [4.9, 1387.0], [5.0, 1387.0], [5.1, 1402.0], [5.2, 1402.0], [5.3, 1422.0], [5.4, 1422.0], [5.5, 1425.0], [5.6, 1425.0], [5.7, 1481.0], [5.8, 1481.0], [5.9, 1488.0], [6.0, 1488.0], [6.1, 1508.0], [6.2, 1508.0], [6.3, 1519.0], [6.4, 1519.0], [6.5, 1537.0], [6.6, 1537.0], [6.7, 1551.0], [6.8, 1551.0], [6.9, 1552.0], [7.0, 1552.0], [7.1, 1643.0], [7.2, 1643.0], [7.3, 1659.0], [7.4, 1659.0], [7.5, 1661.0], [7.6, 1661.0], [7.7, 1703.0], [7.8, 1703.0], [7.9, 1731.0], [8.0, 1731.0], [8.1, 1750.0], [8.2, 1750.0], [8.3, 1764.0], [8.4, 1764.0], [8.5, 1765.0], [8.6, 1765.0], [8.7, 1776.0], [8.8, 1798.0], [8.9, 1798.0], [9.0, 1815.0], [9.1, 1815.0], [9.2, 1820.0], [9.3, 1820.0], [9.4, 1820.0], [9.5, 1820.0], [9.6, 1833.0], [9.7, 1833.0], [9.8, 1863.0], [9.9, 1863.0], [10.0, 1865.0], [10.1, 1865.0], [10.2, 1879.0], [10.3, 1879.0], [10.4, 1903.0], [10.5, 1903.0], [10.6, 1922.0], [10.7, 1922.0], [10.8, 1962.0], [10.9, 1962.0], [11.0, 1975.0], [11.1, 1975.0], [11.2, 1983.0], [11.3, 1983.0], [11.4, 1993.0], [11.5, 1993.0], [11.6, 1995.0], [11.7, 1995.0], [11.8, 2001.0], [11.9, 2001.0], [12.0, 2012.0], [12.1, 2012.0], [12.2, 2018.0], [12.3, 2018.0], [12.4, 2028.0], [12.5, 2028.0], [12.6, 2035.0], [12.7, 2035.0], [12.8, 2050.0], [12.9, 2050.0], [13.0, 2055.0], [13.1, 2055.0], [13.2, 2067.0], [13.3, 2067.0], [13.4, 2100.0], [13.5, 2100.0], [13.6, 2101.0], [13.7, 2101.0], [13.8, 2112.0], [13.9, 2112.0], [14.0, 2122.0], [14.1, 2122.0], [14.2, 2148.0], [14.3, 2148.0], [14.4, 2151.0], [14.5, 2151.0], [14.6, 2152.0], [14.7, 2152.0], [14.8, 2170.0], [14.9, 2170.0], [15.0, 2171.0], [15.1, 2171.0], [15.2, 2177.0], [15.3, 2177.0], [15.4, 2199.0], [15.5, 2199.0], [15.6, 2219.0], [15.7, 2219.0], [15.8, 2228.0], [15.9, 2228.0], [16.0, 2230.0], [16.1, 2230.0], [16.2, 2231.0], [16.3, 2231.0], [16.4, 2258.0], [16.5, 2258.0], [16.6, 2294.0], [16.7, 2294.0], [16.8, 2296.0], [16.9, 2296.0], [17.0, 2296.0], [17.1, 2296.0], [17.2, 2299.0], [17.3, 2299.0], [17.4, 2311.0], [17.5, 2311.0], [17.6, 2315.0], [17.7, 2315.0], [17.8, 2348.0], [17.9, 2348.0], [18.0, 2358.0], [18.1, 2358.0], [18.2, 2363.0], [18.3, 2363.0], [18.4, 2371.0], [18.5, 2371.0], [18.6, 2381.0], [18.7, 2381.0], [18.8, 2382.0], [18.9, 2382.0], [19.0, 2404.0], [19.1, 2404.0], [19.2, 2404.0], [19.3, 2404.0], [19.4, 2409.0], [19.5, 2409.0], [19.6, 2423.0], [19.7, 2423.0], [19.8, 2432.0], [19.9, 2432.0], [20.0, 2450.0], [20.1, 2450.0], [20.2, 2453.0], [20.3, 2453.0], [20.4, 2457.0], [20.5, 2457.0], [20.6, 2463.0], [20.7, 2463.0], [20.8, 2483.0], [20.9, 2483.0], [21.0, 2485.0], [21.1, 2485.0], [21.2, 2487.0], [21.3, 2487.0], [21.4, 2492.0], [21.5, 2492.0], [21.6, 2492.0], [21.7, 2492.0], [21.8, 2499.0], [21.9, 2499.0], [22.0, 2507.0], [22.1, 2507.0], [22.2, 2513.0], [22.3, 2513.0], [22.4, 2519.0], [22.5, 2519.0], [22.6, 2552.0], [22.7, 2552.0], [22.8, 2571.0], [22.9, 2571.0], [23.0, 2576.0], [23.1, 2576.0], [23.2, 2583.0], [23.3, 2583.0], [23.4, 2586.0], [23.5, 2586.0], [23.6, 2604.0], [23.7, 2604.0], [23.8, 2605.0], [23.9, 2605.0], [24.0, 2613.0], [24.1, 2613.0], [24.2, 2616.0], [24.3, 2616.0], [24.4, 2621.0], [24.5, 2621.0], [24.6, 2639.0], [24.7, 2639.0], [24.8, 2643.0], [24.9, 2643.0], [25.0, 2645.0], [25.1, 2645.0], [25.2, 2655.0], [25.3, 2655.0], [25.4, 2656.0], [25.5, 2656.0], [25.6, 2670.0], [25.7, 2670.0], [25.8, 2677.0], [25.9, 2677.0], [26.0, 2687.0], [26.1, 2687.0], [26.2, 2691.0], [26.3, 2691.0], [26.4, 2707.0], [26.5, 2707.0], [26.6, 2718.0], [26.7, 2718.0], [26.8, 2735.0], [26.9, 2735.0], [27.0, 2740.0], [27.1, 2740.0], [27.2, 2744.0], [27.3, 2744.0], [27.4, 2744.0], [27.5, 2744.0], [27.6, 2745.0], [27.7, 2745.0], [27.8, 2747.0], [27.9, 2747.0], [28.0, 2749.0], [28.1, 2749.0], [28.2, 2764.0], [28.3, 2764.0], [28.4, 2783.0], [28.5, 2783.0], [28.6, 2804.0], [28.7, 2804.0], [28.8, 2808.0], [28.9, 2808.0], [29.0, 2811.0], [29.1, 2811.0], [29.2, 2814.0], [29.3, 2814.0], [29.4, 2848.0], [29.5, 2848.0], [29.6, 2871.0], [29.7, 2871.0], [29.8, 2873.0], [29.9, 2873.0], [30.0, 2878.0], [30.1, 2878.0], [30.2, 2889.0], [30.3, 2889.0], [30.4, 2891.0], [30.5, 2891.0], [30.6, 2901.0], [30.7, 2901.0], [30.8, 2902.0], [30.9, 2902.0], [31.0, 2906.0], [31.1, 2906.0], [31.2, 2909.0], [31.3, 2909.0], [31.4, 2909.0], [31.5, 2909.0], [31.6, 2914.0], [31.7, 2914.0], [31.8, 2921.0], [31.9, 2921.0], [32.0, 2923.0], [32.1, 2923.0], [32.2, 2929.0], [32.3, 2929.0], [32.4, 2938.0], [32.5, 2938.0], [32.6, 2939.0], [32.7, 2939.0], [32.8, 2940.0], [32.9, 2940.0], [33.0, 2945.0], [33.1, 2945.0], [33.2, 2948.0], [33.3, 2948.0], [33.4, 2956.0], [33.5, 2956.0], [33.6, 2963.0], [33.7, 2963.0], [33.8, 2978.0], [33.9, 2978.0], [34.0, 2986.0], [34.1, 2986.0], [34.2, 3002.0], [34.3, 3002.0], [34.4, 3020.0], [34.5, 3020.0], [34.6, 3021.0], [34.7, 3021.0], [34.8, 3021.0], [34.9, 3021.0], [35.0, 3023.0], [35.1, 3023.0], [35.2, 3033.0], [35.3, 3033.0], [35.4, 3039.0], [35.5, 3039.0], [35.6, 3050.0], [35.7, 3050.0], [35.8, 3053.0], [35.9, 3053.0], [36.0, 3062.0], [36.1, 3062.0], [36.2, 3072.0], [36.3, 3072.0], [36.4, 3072.0], [36.5, 3072.0], [36.6, 3078.0], [36.7, 3078.0], [36.8, 3083.0], [36.9, 3083.0], [37.0, 3084.0], [37.1, 3084.0], [37.2, 3087.0], [37.3, 3087.0], [37.4, 3099.0], [37.5, 3099.0], [37.6, 3101.0], [37.7, 3101.0], [37.8, 3103.0], [37.9, 3103.0], [38.0, 3104.0], [38.1, 3104.0], [38.2, 3130.0], [38.3, 3130.0], [38.4, 3169.0], [38.5, 3169.0], [38.6, 3174.0], [38.7, 3174.0], [38.8, 3174.0], [38.9, 3174.0], [39.0, 3191.0], [39.1, 3191.0], [39.2, 3196.0], [39.3, 3196.0], [39.4, 3196.0], [39.5, 3201.0], [39.6, 3201.0], [39.7, 3205.0], [39.8, 3205.0], [39.9, 3221.0], [40.0, 3221.0], [40.1, 3222.0], [40.2, 3222.0], [40.3, 3228.0], [40.4, 3228.0], [40.5, 3244.0], [40.6, 3244.0], [40.7, 3245.0], [40.8, 3245.0], [40.9, 3266.0], [41.0, 3266.0], [41.1, 3274.0], [41.2, 3274.0], [41.3, 3300.0], [41.4, 3300.0], [41.5, 3308.0], [41.6, 3308.0], [41.7, 3309.0], [41.8, 3309.0], [41.9, 3316.0], [42.0, 3316.0], [42.1, 3320.0], [42.2, 3320.0], [42.3, 3324.0], [42.4, 3324.0], [42.5, 3330.0], [42.6, 3330.0], [42.7, 3334.0], [42.8, 3334.0], [42.9, 3338.0], [43.0, 3338.0], [43.1, 3341.0], [43.2, 3341.0], [43.3, 3346.0], [43.4, 3346.0], [43.5, 3346.0], [43.6, 3346.0], [43.7, 3348.0], [43.8, 3348.0], [43.9, 3349.0], [44.0, 3349.0], [44.1, 3355.0], [44.2, 3355.0], [44.3, 3358.0], [44.4, 3358.0], [44.5, 3364.0], [44.6, 3364.0], [44.7, 3379.0], [44.8, 3379.0], [44.9, 3380.0], [45.0, 3380.0], [45.1, 3388.0], [45.2, 3388.0], [45.3, 3395.0], [45.4, 3395.0], [45.5, 3401.0], [45.6, 3401.0], [45.7, 3404.0], [45.8, 3404.0], [45.9, 3418.0], [46.0, 3418.0], [46.1, 3436.0], [46.2, 3436.0], [46.3, 3455.0], [46.4, 3455.0], [46.5, 3462.0], [46.6, 3462.0], [46.7, 3470.0], [46.8, 3470.0], [46.9, 3472.0], [47.0, 3472.0], [47.1, 3484.0], [47.2, 3484.0], [47.3, 3488.0], [47.4, 3488.0], [47.5, 3502.0], [47.6, 3502.0], [47.7, 3505.0], [47.8, 3505.0], [47.9, 3512.0], [48.0, 3512.0], [48.1, 3515.0], [48.2, 3515.0], [48.3, 3519.0], [48.4, 3519.0], [48.5, 3527.0], [48.6, 3527.0], [48.7, 3527.0], [48.8, 3527.0], [48.9, 3528.0], [49.0, 3528.0], [49.1, 3531.0], [49.2, 3531.0], [49.3, 3542.0], [49.4, 3542.0], [49.5, 3561.0], [49.6, 3561.0], [49.7, 3570.0], [49.8, 3570.0], [49.9, 3593.0], [50.0, 3593.0], [50.1, 3596.0], [50.2, 3596.0], [50.3, 3603.0], [50.4, 3603.0], [50.5, 3636.0], [50.6, 3636.0], [50.7, 3641.0], [50.8, 3641.0], [50.9, 3645.0], [51.0, 3645.0], [51.1, 3653.0], [51.2, 3653.0], [51.3, 3657.0], [51.4, 3657.0], [51.5, 3666.0], [51.6, 3666.0], [51.7, 3672.0], [51.8, 3672.0], [51.9, 3678.0], [52.0, 3678.0], [52.1, 3680.0], [52.2, 3680.0], [52.3, 3691.0], [52.4, 3691.0], [52.5, 3692.0], [52.6, 3692.0], [52.7, 3696.0], [52.8, 3696.0], [52.9, 3709.0], [53.0, 3709.0], [53.1, 3715.0], [53.2, 3715.0], [53.3, 3722.0], [53.4, 3722.0], [53.5, 3739.0], [53.6, 3739.0], [53.7, 3741.0], [53.8, 3741.0], [53.9, 3748.0], [54.0, 3748.0], [54.1, 3750.0], [54.2, 3750.0], [54.3, 3756.0], [54.4, 3756.0], [54.5, 3775.0], [54.6, 3775.0], [54.7, 3784.0], [54.8, 3784.0], [54.9, 3795.0], [55.0, 3795.0], [55.1, 3806.0], [55.2, 3806.0], [55.3, 3812.0], [55.4, 3812.0], [55.5, 3832.0], [55.6, 3832.0], [55.7, 3837.0], [55.8, 3837.0], [55.9, 3845.0], [56.0, 3845.0], [56.1, 3863.0], [56.2, 3863.0], [56.3, 3868.0], [56.4, 3868.0], [56.5, 3868.0], [56.6, 3868.0], [56.7, 3879.0], [56.8, 3879.0], [56.9, 3881.0], [57.0, 3881.0], [57.1, 3886.0], [57.2, 3886.0], [57.3, 3897.0], [57.4, 3897.0], [57.5, 3913.0], [57.6, 3913.0], [57.7, 3916.0], [57.8, 3916.0], [57.9, 3916.0], [58.0, 3916.0], [58.1, 3920.0], [58.2, 3920.0], [58.3, 3922.0], [58.4, 3922.0], [58.5, 3934.0], [58.6, 3934.0], [58.7, 3939.0], [58.8, 3939.0], [58.9, 3940.0], [59.0, 3940.0], [59.1, 3941.0], [59.2, 3941.0], [59.3, 3947.0], [59.4, 3947.0], [59.5, 3950.0], [59.6, 3950.0], [59.7, 3967.0], [59.8, 3967.0], [59.9, 3970.0], [60.0, 3970.0], [60.1, 3973.0], [60.2, 3973.0], [60.3, 3975.0], [60.4, 3975.0], [60.5, 3975.0], [60.6, 3975.0], [60.7, 3978.0], [60.8, 3978.0], [60.9, 3983.0], [61.0, 3983.0], [61.1, 3996.0], [61.2, 3996.0], [61.3, 3996.0], [61.4, 3996.0], [61.5, 4005.0], [61.6, 4005.0], [61.7, 4022.0], [61.8, 4022.0], [61.9, 4032.0], [62.0, 4032.0], [62.1, 4035.0], [62.2, 4035.0], [62.3, 4070.0], [62.4, 4070.0], [62.5, 4073.0], [62.6, 4073.0], [62.7, 4078.0], [62.8, 4078.0], [62.9, 4091.0], [63.0, 4091.0], [63.1, 4092.0], [63.2, 4092.0], [63.3, 4093.0], [63.4, 4093.0], [63.5, 4105.0], [63.6, 4105.0], [63.7, 4105.0], [63.8, 4105.0], [63.9, 4108.0], [64.0, 4108.0], [64.1, 4109.0], [64.2, 4109.0], [64.3, 4151.0], [64.4, 4151.0], [64.5, 4184.0], [64.6, 4184.0], [64.7, 4188.0], [64.8, 4188.0], [64.9, 4207.0], [65.0, 4207.0], [65.1, 4207.0], [65.2, 4207.0], [65.3, 4210.0], [65.4, 4210.0], [65.5, 4213.0], [65.6, 4213.0], [65.7, 4213.0], [65.8, 4213.0], [65.9, 4222.0], [66.0, 4222.0], [66.1, 4223.0], [66.2, 4223.0], [66.3, 4238.0], [66.4, 4238.0], [66.5, 4261.0], [66.6, 4261.0], [66.7, 4271.0], [66.8, 4271.0], [66.9, 4292.0], [67.0, 4292.0], [67.1, 4302.0], [67.2, 4302.0], [67.3, 4313.0], [67.4, 4313.0], [67.5, 4317.0], [67.6, 4317.0], [67.7, 4323.0], [67.8, 4323.0], [67.9, 4326.0], [68.0, 4326.0], [68.1, 4337.0], [68.2, 4337.0], [68.3, 4342.0], [68.4, 4342.0], [68.5, 4346.0], [68.6, 4346.0], [68.7, 4352.0], [68.8, 4352.0], [68.9, 4354.0], [69.0, 4354.0], [69.1, 4354.0], [69.2, 4354.0], [69.3, 4366.0], [69.4, 4366.0], [69.5, 4367.0], [69.6, 4367.0], [69.7, 4381.0], [69.8, 4381.0], [69.9, 4383.0], [70.0, 4383.0], [70.1, 4417.0], [70.2, 4417.0], [70.3, 4418.0], [70.4, 4418.0], [70.5, 4425.0], [70.6, 4425.0], [70.7, 4430.0], [70.8, 4430.0], [70.9, 4441.0], [71.0, 4441.0], [71.1, 4445.0], [71.2, 4445.0], [71.3, 4447.0], [71.4, 4447.0], [71.5, 4456.0], [71.6, 4456.0], [71.7, 4466.0], [71.8, 4466.0], [71.9, 4473.0], [72.0, 4473.0], [72.1, 4473.0], [72.2, 4473.0], [72.3, 4477.0], [72.4, 4477.0], [72.5, 4478.0], [72.6, 4478.0], [72.7, 4482.0], [72.8, 4482.0], [72.9, 4495.0], [73.0, 4495.0], [73.1, 4495.0], [73.2, 4495.0], [73.3, 4498.0], [73.4, 4498.0], [73.5, 4510.0], [73.6, 4510.0], [73.7, 4515.0], [73.8, 4515.0], [73.9, 4526.0], [74.0, 4526.0], [74.1, 4527.0], [74.2, 4527.0], [74.3, 4528.0], [74.4, 4528.0], [74.5, 4528.0], [74.6, 4528.0], [74.7, 4572.0], [74.8, 4572.0], [74.9, 4589.0], [75.0, 4589.0], [75.1, 4615.0], [75.2, 4615.0], [75.3, 4615.0], [75.4, 4615.0], [75.5, 4621.0], [75.6, 4621.0], [75.7, 4640.0], [75.8, 4640.0], [75.9, 4658.0], [76.0, 4658.0], [76.1, 4662.0], [76.2, 4662.0], [76.3, 4679.0], [76.4, 4679.0], [76.5, 4681.0], [76.6, 4681.0], [76.7, 4687.0], [76.8, 4687.0], [76.9, 4689.0], [77.0, 4689.0], [77.1, 4694.0], [77.2, 4694.0], [77.3, 4694.0], [77.4, 4694.0], [77.5, 4696.0], [77.6, 4696.0], [77.7, 4703.0], [77.8, 4703.0], [77.9, 4706.0], [78.0, 4706.0], [78.1, 4716.0], [78.2, 4716.0], [78.3, 4729.0], [78.4, 4729.0], [78.5, 4764.0], [78.6, 4764.0], [78.7, 4769.0], [78.8, 4769.0], [78.9, 4775.0], [79.0, 4775.0], [79.1, 4787.0], [79.2, 4787.0], [79.3, 4789.0], [79.4, 4789.0], [79.5, 4793.0], [79.6, 4793.0], [79.7, 4794.0], [79.8, 4794.0], [79.9, 4815.0], [80.0, 4815.0], [80.1, 4818.0], [80.2, 4818.0], [80.3, 4825.0], [80.4, 4825.0], [80.5, 4825.0], [80.6, 4825.0], [80.7, 4846.0], [80.8, 4846.0], [80.9, 4847.0], [81.0, 4847.0], [81.1, 4850.0], [81.2, 4850.0], [81.3, 4856.0], [81.4, 4856.0], [81.5, 4868.0], [81.6, 4868.0], [81.7, 4889.0], [81.8, 4889.0], [81.9, 4902.0], [82.0, 4902.0], [82.1, 4907.0], [82.2, 4907.0], [82.3, 4911.0], [82.4, 4911.0], [82.5, 4922.0], [82.6, 4922.0], [82.7, 4933.0], [82.8, 4933.0], [82.9, 4944.0], [83.0, 4944.0], [83.1, 4963.0], [83.2, 4963.0], [83.3, 4979.0], [83.4, 4979.0], [83.5, 4983.0], [83.6, 4983.0], [83.7, 4998.0], [83.8, 4998.0], [83.9, 5006.0], [84.0, 5006.0], [84.1, 5014.0], [84.2, 5014.0], [84.3, 5021.0], [84.4, 5021.0], [84.5, 5047.0], [84.6, 5047.0], [84.7, 5052.0], [84.8, 5052.0], [84.9, 5061.0], [85.0, 5061.0], [85.1, 5069.0], [85.2, 5069.0], [85.3, 5081.0], [85.4, 5081.0], [85.5, 5088.0], [85.6, 5088.0], [85.7, 5119.0], [85.8, 5119.0], [85.9, 5132.0], [86.0, 5132.0], [86.1, 5152.0], [86.2, 5152.0], [86.3, 5152.0], [86.4, 5152.0], [86.5, 5156.0], [86.6, 5156.0], [86.7, 5165.0], [86.8, 5165.0], [86.9, 5199.0], [87.0, 5199.0], [87.1, 5219.0], [87.2, 5219.0], [87.3, 5229.0], [87.4, 5229.0], [87.5, 5229.0], [87.6, 5229.0], [87.7, 5249.0], [87.8, 5249.0], [87.9, 5261.0], [88.0, 5261.0], [88.1, 5270.0], [88.2, 5270.0], [88.3, 5284.0], [88.4, 5284.0], [88.5, 5310.0], [88.6, 5310.0], [88.7, 5352.0], [88.8, 5352.0], [88.9, 5503.0], [89.0, 5503.0], [89.1, 5515.0], [89.2, 5515.0], [89.3, 5558.0], [89.4, 5558.0], [89.5, 5566.0], [89.6, 5566.0], [89.7, 5580.0], [89.8, 5580.0], [89.9, 5615.0], [90.0, 5615.0], [90.1, 5636.0], [90.2, 5636.0], [90.3, 5701.0], [90.4, 5701.0], [90.5, 5796.0], [90.6, 5796.0], [90.7, 5802.0], [90.8, 5802.0], [90.9, 5882.0], [91.0, 5882.0], [91.1, 6074.0], [91.2, 6074.0], [91.3, 6083.0], [91.4, 6083.0], [91.5, 6085.0], [91.6, 6085.0], [91.7, 6090.0], [91.8, 6090.0], [91.9, 6095.0], [92.0, 6095.0], [92.1, 6096.0], [92.2, 6096.0], [92.3, 6098.0], [92.4, 6098.0], [92.5, 6141.0], [92.6, 6141.0], [92.7, 6188.0], [92.8, 6188.0], [92.9, 6213.0], [93.0, 6213.0], [93.1, 6234.0], [93.2, 6234.0], [93.3, 6237.0], [93.4, 6237.0], [93.5, 6243.0], [93.6, 6243.0], [93.7, 6281.0], [93.8, 6281.0], [93.9, 6315.0], [94.0, 6315.0], [94.1, 6317.0], [94.2, 6317.0], [94.3, 6405.0], [94.4, 6405.0], [94.5, 6429.0], [94.6, 6429.0], [94.7, 6436.0], [94.8, 6436.0], [94.9, 6470.0], [95.0, 6470.0], [95.1, 6498.0], [95.2, 6498.0], [95.3, 6532.0], [95.4, 6532.0], [95.5, 6536.0], [95.6, 6536.0], [95.7, 6577.0], [95.8, 6577.0], [95.9, 6732.0], [96.0, 6732.0], [96.1, 6852.0], [96.2, 6852.0], [96.3, 7044.0], [96.4, 7044.0], [96.5, 7131.0], [96.6, 7131.0], [96.7, 7274.0], [96.8, 7274.0], [96.9, 7359.0], [97.0, 7359.0], [97.1, 7618.0], [97.2, 7618.0], [97.3, 7678.0], [97.4, 7678.0], [97.5, 7974.0], [97.6, 7974.0], [97.7, 8152.0], [97.8, 8152.0], [97.9, 8359.0], [98.0, 8359.0], [98.1, 8668.0], [98.2, 8668.0], [98.3, 8674.0], [98.4, 8674.0], [98.5, 8966.0], [98.6, 8966.0], [98.7, 9704.0], [98.8, 9704.0], [98.9, 9735.0], [99.0, 9735.0], [99.1, 10251.0], [99.2, 10251.0], [99.3, 10757.0], [99.4, 10757.0], [99.5, 11219.0], [99.6, 11219.0], [99.7, 11678.0], [99.8, 11678.0], [99.9, 12743.0], [100.0, 12743.0]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "maxX": 100.0, "title": "Response Time Percentiles"}},
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
        data: {"result": {"minY": 1.0, "minX": 300.0, "maxY": 21.0, "series": [{"data": [[700.0, 3.0], [800.0, 1.0], [900.0, 4.0], [1000.0, 3.0], [1100.0, 2.0], [1200.0, 2.0], [1300.0, 3.0], [1400.0, 5.0], [1500.0, 5.0], [1600.0, 3.0], [1700.0, 7.0], [1800.0, 7.0], [1900.0, 7.0], [2000.0, 8.0], [2100.0, 11.0], [2200.0, 9.0], [2300.0, 8.0], [2400.0, 15.0], [2500.0, 8.0], [2600.0, 14.0], [2700.0, 11.0], [2800.0, 10.0], [2900.0, 18.0], [3000.0, 17.0], [3100.0, 9.0], [3200.0, 9.0], [3300.0, 21.0], [3400.0, 10.0], [3500.0, 14.0], [3700.0, 11.0], [3600.0, 13.0], [3800.0, 12.0], [3900.0, 20.0], [4000.0, 10.0], [4300.0, 15.0], [4200.0, 11.0], [4100.0, 7.0], [4400.0, 17.0], [4600.0, 13.0], [4500.0, 8.0], [4800.0, 10.0], [4700.0, 11.0], [5100.0, 7.0], [5000.0, 9.0], [4900.0, 10.0], [5200.0, 7.0], [5300.0, 2.0], [5500.0, 5.0], [5600.0, 2.0], [5800.0, 2.0], [5700.0, 2.0], [6000.0, 7.0], [6100.0, 2.0], [6200.0, 5.0], [6300.0, 2.0], [6400.0, 5.0], [6500.0, 3.0], [6800.0, 1.0], [6700.0, 1.0], [7100.0, 1.0], [7000.0, 1.0], [7200.0, 1.0], [7300.0, 1.0], [7600.0, 2.0], [7900.0, 1.0], [8100.0, 1.0], [8600.0, 2.0], [8300.0, 1.0], [8900.0, 1.0], [9700.0, 2.0], [10200.0, 1.0], [10700.0, 1.0], [11200.0, 1.0], [11600.0, 1.0], [12700.0, 1.0], [300.0, 3.0], [400.0, 1.0], [500.0, 3.0]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 100, "maxX": 12700.0, "title": "Response Time Distribution"}},
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
        data: {"result": {"minY": 4.0, "minX": 0.0, "ticks": [[0, "Requests having \nresponse time <= 500ms"], [1, "Requests having \nresponse time > 500ms and <= 1,500ms"], [2, "Requests having \nresponse time > 1,500ms"], [3, "Requests in error"]], "maxY": 460.0, "series": [{"data": [[0.0, 4.0]], "color": "#9ACD32", "isOverall": false, "label": "Requests having \nresponse time <= 500ms", "isController": false}, {"data": [[1.0, 26.0]], "color": "yellow", "isOverall": false, "label": "Requests having \nresponse time > 500ms and <= 1,500ms", "isController": false}, {"data": [[2.0, 460.0]], "color": "orange", "isOverall": false, "label": "Requests having \nresponse time > 1,500ms", "isController": false}, {"data": [[3.0, 10.0]], "color": "#FF6347", "isOverall": false, "label": "Requests in error", "isController": false}], "supportsControllersDiscrimination": false, "maxX": 3.0, "title": "Synthetic Response Times Distribution"}},
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
        data: {"result": {"minY": 43.47258485639689, "minX": 1.79103414E12, "maxY": 50.0, "series": [{"data": [[1.79103414E12, 50.0], [1.7910342E12, 43.47258485639689]], "isOverall": false, "label": "Authenticated users", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.7910342E12, "title": "Active Threads Over Time"}},
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
        data: {"result": {"minY": 522.0, "minX": 1.0, "maxY": 4081.1030303030257, "series": [{"data": [[33.0, 3700.5], [32.0, 2067.0], [2.0, 522.0], [35.0, 2937.3333333333335], [34.0, 2921.6666666666665], [37.0, 3564.75], [36.0, 2399.5], [39.0, 3099.0], [38.0, 2731.0], [41.0, 2725.0], [40.0, 3221.6], [43.0, 4024.636363636364], [42.0, 3190.6666666666665], [45.0, 3873.0], [44.0, 3819.8888888888887], [47.0, 3551.2], [46.0, 3565.0], [49.0, 3977.8181818181824], [48.0, 3491.6363636363635], [3.0, 525.0], [50.0, 4081.1030303030257], [4.0, 794.0], [5.0, 904.0], [6.0, 835.6666666666666], [7.0, 2050.0], [8.0, 1488.0], [9.0, 2018.0], [10.0, 1023.0], [11.0, 1045.0], [12.0, 2148.0], [13.0, 1071.5], [14.0, 1949.6666666666667], [15.0, 1380.0], [16.0, 1422.0], [1.0, 2831.8], [17.0, 1899.0], [18.0, 2249.6666666666665], [19.0, 2147.5], [20.0, 1552.0], [21.0, 2978.0], [24.0, 1976.3333333333333], [25.0, 2235.0], [26.0, 2583.0], [27.0, 2432.0], [28.0, 2724.5], [29.0, 2871.0], [30.0, 2887.0], [31.0, 3455.0]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}, {"data": [[44.99999999999998, 3747.459999999996]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-Aggregated", "isController": false}], "supportsControllersDiscrimination": true, "maxX": 50.0, "title": "Time VS Threads"}},
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
        data : {"result": {"minY": 1684.8, "minX": 1.79103414E12, "maxY": 1917717.0, "series": [{"data": [[1.79103414E12, 558524.2833333333], [1.7910342E12, 1917717.0]], "isOverall": false, "label": "Bytes received per second", "isController": false}, {"data": [[1.79103414E12, 1684.8], [1.7910342E12, 5515.2]], "isOverall": false, "label": "Bytes sent per second", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.7910342E12, "title": "Bytes Throughput Over Time"}},
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
        data: {"result": {"minY": 3557.9686684073095, "minX": 1.79103414E12, "maxY": 4367.760683760682, "series": [{"data": [[1.79103414E12, 4367.760683760682], [1.7910342E12, 3557.9686684073095]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.7910342E12, "title": "Response Time Over Time"}},
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
        data: {"result": {"minY": 3553.780678851175, "minX": 1.79103414E12, "maxY": 4351.6410256410245, "series": [{"data": [[1.79103414E12, 4351.6410256410245], [1.7910342E12, 3553.780678851175]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.7910342E12, "title": "Latencies Over Time"}},
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
        data: {"result": {"minY": 0.057441253263707595, "minX": 1.79103414E12, "maxY": 1.863247863247863, "series": [{"data": [[1.79103414E12, 1.863247863247863], [1.7910342E12, 0.057441253263707595]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.7910342E12, "title": "Connect Time Over Time"}},
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
        data: {"result": {"minY": 309.0, "minX": 1.79103414E12, "maxY": 11678.0, "series": [{"data": [[1.79103414E12, 11678.0], [1.7910342E12, 11219.0]], "isOverall": false, "label": "Max", "isController": false}, {"data": [[1.79103414E12, 799.0], [1.7910342E12, 309.0]], "isOverall": false, "label": "Min", "isController": false}, {"data": [[1.79103414E12, 7005.599999999999], [1.7910342E12, 5047.0]], "isOverall": false, "label": "90th percentile", "isController": false}, {"data": [[1.79103414E12, 11567.479999999996], [1.7910342E12, 8050.999999999995]], "isOverall": false, "label": "99th percentile", "isController": false}, {"data": [[1.79103414E12, 3939.0], [1.7910342E12, 3512.0]], "isOverall": false, "label": "Median", "isController": false}, {"data": [[1.79103414E12, 9261.199999999993], [1.7910342E12, 5701.0]], "isOverall": false, "label": "95th percentile", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.7910342E12, "title": "Response Time Percentiles Over Time (successful requests only)"}},
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
    data: {"result": {"minY": 343.0, "minX": 1.0, "maxY": 12743.0, "series": [{"data": [[8.0, 3355.0], [2.0, 522.0], [9.0, 1855.5], [10.0, 4127.0], [11.0, 4367.5], [12.0, 3846.0], [3.0, 343.0], [13.0, 3544.5], [14.0, 3848.5], [15.0, 3666.0], [4.0, 3786.5], [1.0, 403.0], [17.0, 3472.0], [18.0, 2371.5], [19.0, 4040.5], [20.0, 3922.0], [5.0, 3465.5], [21.0, 3715.0], [22.0, 3329.0], [6.0, 4621.0], [7.0, 2682.5]], "isOverall": false, "label": "Successes", "isController": false}, {"data": [[2.0, 12743.0], [9.0, 6474.0], [20.0, 3087.0], [5.0, 4615.0], [11.0, 8668.0], [12.0, 4579.0], [6.0, 7131.0], [15.0, 2929.0]], "isOverall": false, "label": "Failures", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 1000, "maxX": 22.0, "title": "Response Time Vs Request"}},
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
    data: {"result": {"minY": 340.0, "minX": 1.0, "maxY": 12739.0, "series": [{"data": [[8.0, 3351.0], [2.0, 516.0], [9.0, 1852.5], [10.0, 4120.5], [11.0, 4363.0], [12.0, 3843.0], [3.0, 340.0], [13.0, 3541.0], [14.0, 3843.0], [15.0, 3662.0], [4.0, 3781.5], [1.0, 401.0], [17.0, 3470.0], [18.0, 2366.5], [19.0, 4038.0], [20.0, 3919.0], [5.0, 3462.0], [21.0, 3711.0], [22.0, 3325.5], [6.0, 4613.0], [7.0, 2679.0]], "isOverall": false, "label": "Successes", "isController": false}, {"data": [[2.0, 12739.0], [9.0, 6365.0], [20.0, 3079.0], [5.0, 4609.0], [11.0, 8153.0], [12.0, 4346.5], [6.0, 7084.0], [15.0, 2922.0]], "isOverall": false, "label": "Failures", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 1000, "maxX": 22.0, "title": "Latencies Vs Request"}},
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
        data: {"result": {"minY": 2.783333333333333, "minX": 1.79103414E12, "maxY": 5.55, "series": [{"data": [[1.79103414E12, 2.783333333333333], [1.7910342E12, 5.55]], "isOverall": false, "label": "hitsPerSecond", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.7910342E12, "title": "Hits Per Second"}},
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
        data: {"result": {"minY": 0.06666666666666667, "minX": 1.79103414E12, "maxY": 6.316666666666666, "series": [{"data": [[1.79103414E12, 1.85], [1.7910342E12, 6.316666666666666]], "isOverall": false, "label": "200", "isController": false}, {"data": [[1.79103414E12, 0.1], [1.7910342E12, 0.06666666666666667]], "isOverall": false, "label": "500", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.7910342E12, "title": "Codes Per Second"}},
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
        data: {"result": {"minY": 0.06666666666666667, "minX": 1.79103414E12, "maxY": 6.316666666666666, "series": [{"data": [[1.79103414E12, 1.85], [1.7910342E12, 6.316666666666666]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-success", "isController": false}, {"data": [[1.79103414E12, 0.1], [1.7910342E12, 0.06666666666666667]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-failure", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.7910342E12, "title": "Transactions Per Second"}},
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
        data: {"result": {"minY": 0.06666666666666667, "minX": 1.79103414E12, "maxY": 6.316666666666666, "series": [{"data": [[1.79103414E12, 1.85], [1.7910342E12, 6.316666666666666]], "isOverall": false, "label": "Transaction-success", "isController": false}, {"data": [[1.79103414E12, 0.1], [1.7910342E12, 0.06666666666666667]], "isOverall": false, "label": "Transaction-failure", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.7910342E12, "title": "Total Transactions Per Second"}},
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

