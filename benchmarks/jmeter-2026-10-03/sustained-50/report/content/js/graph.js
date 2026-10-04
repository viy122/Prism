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
        data: {"result": {"minY": 627.0, "minX": 0.0, "maxY": 60019.0, "series": [{"data": [[0.0, 627.0], [0.1, 910.0], [0.2, 983.0], [0.3, 1008.0], [0.4, 1116.0], [0.5, 1148.0], [0.6, 1174.0], [0.7, 1220.0], [0.8, 1257.0], [0.9, 1281.0], [1.0, 1295.0], [1.1, 1312.0], [1.2, 1331.0], [1.3, 1343.0], [1.4, 1359.0], [1.5, 1370.0], [1.6, 1409.0], [1.7, 1416.0], [1.8, 1429.0], [1.9, 1441.0], [2.0, 1477.0], [2.1, 1513.0], [2.2, 1525.0], [2.3, 1567.0], [2.4, 1580.0], [2.5, 1597.0], [2.6, 1624.0], [2.7, 1649.0], [2.8, 1668.0], [2.9, 1696.0], [3.0, 1726.0], [3.1, 1735.0], [3.2, 1749.0], [3.3, 1764.0], [3.4, 1775.0], [3.5, 1785.0], [3.6, 1805.0], [3.7, 1811.0], [3.8, 1816.0], [3.9, 1829.0], [4.0, 1847.0], [4.1, 1856.0], [4.2, 1873.0], [4.3, 1885.0], [4.4, 1901.0], [4.5, 1905.0], [4.6, 1913.0], [4.7, 1928.0], [4.8, 1935.0], [4.9, 1947.0], [5.0, 1966.0], [5.1, 1975.0], [5.2, 1978.0], [5.3, 2002.0], [5.4, 2028.0], [5.5, 2033.0], [5.6, 2047.0], [5.7, 2052.0], [5.8, 2066.0], [5.9, 2074.0], [6.0, 2092.0], [6.1, 2100.0], [6.2, 2106.0], [6.3, 2116.0], [6.4, 2126.0], [6.5, 2140.0], [6.6, 2144.0], [6.7, 2147.0], [6.8, 2160.0], [6.9, 2162.0], [7.0, 2165.0], [7.1, 2172.0], [7.2, 2175.0], [7.3, 2186.0], [7.4, 2189.0], [7.5, 2203.0], [7.6, 2205.0], [7.7, 2217.0], [7.8, 2221.0], [7.9, 2229.0], [8.0, 2233.0], [8.1, 2235.0], [8.2, 2256.0], [8.3, 2261.0], [8.4, 2268.0], [8.5, 2273.0], [8.6, 2277.0], [8.7, 2282.0], [8.8, 2287.0], [8.9, 2292.0], [9.0, 2295.0], [9.1, 2305.0], [9.2, 2310.0], [9.3, 2317.0], [9.4, 2325.0], [9.5, 2326.0], [9.6, 2332.0], [9.7, 2339.0], [9.8, 2344.0], [9.9, 2353.0], [10.0, 2359.0], [10.1, 2369.0], [10.2, 2374.0], [10.3, 2375.0], [10.4, 2382.0], [10.5, 2397.0], [10.6, 2400.0], [10.7, 2402.0], [10.8, 2406.0], [10.9, 2410.0], [11.0, 2414.0], [11.1, 2416.0], [11.2, 2424.0], [11.3, 2429.0], [11.4, 2434.0], [11.5, 2443.0], [11.6, 2449.0], [11.7, 2453.0], [11.8, 2459.0], [11.9, 2473.0], [12.0, 2478.0], [12.1, 2485.0], [12.2, 2490.0], [12.3, 2493.0], [12.4, 2499.0], [12.5, 2501.0], [12.6, 2506.0], [12.7, 2509.0], [12.8, 2514.0], [12.9, 2518.0], [13.0, 2519.0], [13.1, 2522.0], [13.2, 2531.0], [13.3, 2535.0], [13.4, 2541.0], [13.5, 2545.0], [13.6, 2549.0], [13.7, 2551.0], [13.8, 2560.0], [13.9, 2566.0], [14.0, 2569.0], [14.1, 2574.0], [14.2, 2581.0], [14.3, 2583.0], [14.4, 2585.0], [14.5, 2589.0], [14.6, 2604.0], [14.7, 2607.0], [14.8, 2620.0], [14.9, 2630.0], [15.0, 2639.0], [15.1, 2641.0], [15.2, 2649.0], [15.3, 2653.0], [15.4, 2656.0], [15.5, 2664.0], [15.6, 2666.0], [15.7, 2670.0], [15.8, 2676.0], [15.9, 2681.0], [16.0, 2685.0], [16.1, 2689.0], [16.2, 2693.0], [16.3, 2696.0], [16.4, 2700.0], [16.5, 2702.0], [16.6, 2706.0], [16.7, 2710.0], [16.8, 2714.0], [16.9, 2716.0], [17.0, 2720.0], [17.1, 2724.0], [17.2, 2726.0], [17.3, 2728.0], [17.4, 2733.0], [17.5, 2735.0], [17.6, 2740.0], [17.7, 2743.0], [17.8, 2745.0], [17.9, 2754.0], [18.0, 2757.0], [18.1, 2760.0], [18.2, 2764.0], [18.3, 2772.0], [18.4, 2774.0], [18.5, 2778.0], [18.6, 2791.0], [18.7, 2792.0], [18.8, 2795.0], [18.9, 2798.0], [19.0, 2801.0], [19.1, 2804.0], [19.2, 2807.0], [19.3, 2814.0], [19.4, 2818.0], [19.5, 2821.0], [19.6, 2822.0], [19.7, 2824.0], [19.8, 2829.0], [19.9, 2834.0], [20.0, 2836.0], [20.1, 2839.0], [20.2, 2846.0], [20.3, 2848.0], [20.4, 2852.0], [20.5, 2860.0], [20.6, 2862.0], [20.7, 2866.0], [20.8, 2869.0], [20.9, 2870.0], [21.0, 2873.0], [21.1, 2877.0], [21.2, 2882.0], [21.3, 2883.0], [21.4, 2886.0], [21.5, 2887.0], [21.6, 2890.0], [21.7, 2893.0], [21.8, 2897.0], [21.9, 2899.0], [22.0, 2903.0], [22.1, 2906.0], [22.2, 2906.0], [22.3, 2909.0], [22.4, 2912.0], [22.5, 2914.0], [22.6, 2916.0], [22.7, 2919.0], [22.8, 2922.0], [22.9, 2924.0], [23.0, 2930.0], [23.1, 2933.0], [23.2, 2935.0], [23.3, 2938.0], [23.4, 2942.0], [23.5, 2946.0], [23.6, 2949.0], [23.7, 2952.0], [23.8, 2954.0], [23.9, 2959.0], [24.0, 2962.0], [24.1, 2964.0], [24.2, 2966.0], [24.3, 2969.0], [24.4, 2978.0], [24.5, 2982.0], [24.6, 2985.0], [24.7, 2992.0], [24.8, 2996.0], [24.9, 2998.0], [25.0, 3002.0], [25.1, 3004.0], [25.2, 3008.0], [25.3, 3010.0], [25.4, 3013.0], [25.5, 3015.0], [25.6, 3022.0], [25.7, 3026.0], [25.8, 3029.0], [25.9, 3032.0], [26.0, 3033.0], [26.1, 3041.0], [26.2, 3042.0], [26.3, 3048.0], [26.4, 3049.0], [26.5, 3052.0], [26.6, 3057.0], [26.7, 3059.0], [26.8, 3060.0], [26.9, 3063.0], [27.0, 3068.0], [27.1, 3073.0], [27.2, 3075.0], [27.3, 3077.0], [27.4, 3080.0], [27.5, 3082.0], [27.6, 3086.0], [27.7, 3088.0], [27.8, 3091.0], [27.9, 3094.0], [28.0, 3094.0], [28.1, 3100.0], [28.2, 3103.0], [28.3, 3108.0], [28.4, 3109.0], [28.5, 3112.0], [28.6, 3116.0], [28.7, 3120.0], [28.8, 3124.0], [28.9, 3126.0], [29.0, 3128.0], [29.1, 3133.0], [29.2, 3140.0], [29.3, 3143.0], [29.4, 3146.0], [29.5, 3146.0], [29.6, 3149.0], [29.7, 3154.0], [29.8, 3160.0], [29.9, 3166.0], [30.0, 3170.0], [30.1, 3175.0], [30.2, 3180.0], [30.3, 3182.0], [30.4, 3185.0], [30.5, 3186.0], [30.6, 3190.0], [30.7, 3195.0], [30.8, 3196.0], [30.9, 3200.0], [31.0, 3201.0], [31.1, 3206.0], [31.2, 3207.0], [31.3, 3212.0], [31.4, 3215.0], [31.5, 3218.0], [31.6, 3220.0], [31.7, 3222.0], [31.8, 3224.0], [31.9, 3227.0], [32.0, 3228.0], [32.1, 3229.0], [32.2, 3232.0], [32.3, 3237.0], [32.4, 3241.0], [32.5, 3245.0], [32.6, 3249.0], [32.7, 3251.0], [32.8, 3256.0], [32.9, 3259.0], [33.0, 3263.0], [33.1, 3267.0], [33.2, 3269.0], [33.3, 3270.0], [33.4, 3272.0], [33.5, 3278.0], [33.6, 3285.0], [33.7, 3289.0], [33.8, 3292.0], [33.9, 3295.0], [34.0, 3298.0], [34.1, 3303.0], [34.2, 3304.0], [34.3, 3306.0], [34.4, 3309.0], [34.5, 3312.0], [34.6, 3314.0], [34.7, 3316.0], [34.8, 3323.0], [34.9, 3325.0], [35.0, 3328.0], [35.1, 3330.0], [35.2, 3333.0], [35.3, 3336.0], [35.4, 3337.0], [35.5, 3343.0], [35.6, 3344.0], [35.7, 3346.0], [35.8, 3348.0], [35.9, 3353.0], [36.0, 3357.0], [36.1, 3361.0], [36.2, 3363.0], [36.3, 3367.0], [36.4, 3369.0], [36.5, 3374.0], [36.6, 3376.0], [36.7, 3379.0], [36.8, 3381.0], [36.9, 3384.0], [37.0, 3387.0], [37.1, 3390.0], [37.2, 3395.0], [37.3, 3397.0], [37.4, 3401.0], [37.5, 3402.0], [37.6, 3407.0], [37.7, 3409.0], [37.8, 3413.0], [37.9, 3418.0], [38.0, 3419.0], [38.1, 3422.0], [38.2, 3425.0], [38.3, 3426.0], [38.4, 3430.0], [38.5, 3433.0], [38.6, 3435.0], [38.7, 3436.0], [38.8, 3443.0], [38.9, 3446.0], [39.0, 3447.0], [39.1, 3452.0], [39.2, 3453.0], [39.3, 3455.0], [39.4, 3457.0], [39.5, 3462.0], [39.6, 3465.0], [39.7, 3469.0], [39.8, 3472.0], [39.9, 3473.0], [40.0, 3479.0], [40.1, 3481.0], [40.2, 3485.0], [40.3, 3488.0], [40.4, 3491.0], [40.5, 3496.0], [40.6, 3499.0], [40.7, 3501.0], [40.8, 3506.0], [40.9, 3510.0], [41.0, 3513.0], [41.1, 3517.0], [41.2, 3519.0], [41.3, 3521.0], [41.4, 3523.0], [41.5, 3526.0], [41.6, 3531.0], [41.7, 3534.0], [41.8, 3537.0], [41.9, 3539.0], [42.0, 3543.0], [42.1, 3551.0], [42.2, 3552.0], [42.3, 3554.0], [42.4, 3556.0], [42.5, 3560.0], [42.6, 3562.0], [42.7, 3563.0], [42.8, 3565.0], [42.9, 3567.0], [43.0, 3571.0], [43.1, 3574.0], [43.2, 3576.0], [43.3, 3579.0], [43.4, 3582.0], [43.5, 3585.0], [43.6, 3588.0], [43.7, 3591.0], [43.8, 3599.0], [43.9, 3601.0], [44.0, 3608.0], [44.1, 3613.0], [44.2, 3615.0], [44.3, 3618.0], [44.4, 3619.0], [44.5, 3619.0], [44.6, 3621.0], [44.7, 3622.0], [44.8, 3624.0], [44.9, 3627.0], [45.0, 3631.0], [45.1, 3634.0], [45.2, 3636.0], [45.3, 3637.0], [45.4, 3639.0], [45.5, 3643.0], [45.6, 3644.0], [45.7, 3645.0], [45.8, 3649.0], [45.9, 3654.0], [46.0, 3655.0], [46.1, 3658.0], [46.2, 3665.0], [46.3, 3672.0], [46.4, 3677.0], [46.5, 3677.0], [46.6, 3680.0], [46.7, 3685.0], [46.8, 3689.0], [46.9, 3695.0], [47.0, 3697.0], [47.1, 3700.0], [47.2, 3703.0], [47.3, 3707.0], [47.4, 3708.0], [47.5, 3710.0], [47.6, 3716.0], [47.7, 3718.0], [47.8, 3720.0], [47.9, 3724.0], [48.0, 3726.0], [48.1, 3730.0], [48.2, 3733.0], [48.3, 3735.0], [48.4, 3739.0], [48.5, 3741.0], [48.6, 3743.0], [48.7, 3746.0], [48.8, 3749.0], [48.9, 3753.0], [49.0, 3759.0], [49.1, 3762.0], [49.2, 3764.0], [49.3, 3767.0], [49.4, 3769.0], [49.5, 3775.0], [49.6, 3777.0], [49.7, 3778.0], [49.8, 3780.0], [49.9, 3781.0], [50.0, 3783.0], [50.1, 3785.0], [50.2, 3787.0], [50.3, 3789.0], [50.4, 3793.0], [50.5, 3797.0], [50.6, 3804.0], [50.7, 3808.0], [50.8, 3810.0], [50.9, 3813.0], [51.0, 3816.0], [51.1, 3819.0], [51.2, 3822.0], [51.3, 3824.0], [51.4, 3830.0], [51.5, 3835.0], [51.6, 3840.0], [51.7, 3843.0], [51.8, 3849.0], [51.9, 3851.0], [52.0, 3853.0], [52.1, 3856.0], [52.2, 3863.0], [52.3, 3867.0], [52.4, 3869.0], [52.5, 3872.0], [52.6, 3875.0], [52.7, 3884.0], [52.8, 3891.0], [52.9, 3891.0], [53.0, 3895.0], [53.1, 3897.0], [53.2, 3899.0], [53.3, 3903.0], [53.4, 3907.0], [53.5, 3910.0], [53.6, 3914.0], [53.7, 3916.0], [53.8, 3917.0], [53.9, 3921.0], [54.0, 3922.0], [54.1, 3924.0], [54.2, 3926.0], [54.3, 3928.0], [54.4, 3933.0], [54.5, 3937.0], [54.6, 3941.0], [54.7, 3945.0], [54.8, 3949.0], [54.9, 3950.0], [55.0, 3957.0], [55.1, 3958.0], [55.2, 3961.0], [55.3, 3968.0], [55.4, 3979.0], [55.5, 3981.0], [55.6, 3983.0], [55.7, 3986.0], [55.8, 3993.0], [55.9, 3995.0], [56.0, 4002.0], [56.1, 4004.0], [56.2, 4008.0], [56.3, 4018.0], [56.4, 4020.0], [56.5, 4021.0], [56.6, 4024.0], [56.7, 4026.0], [56.8, 4028.0], [56.9, 4029.0], [57.0, 4031.0], [57.1, 4036.0], [57.2, 4040.0], [57.3, 4046.0], [57.4, 4050.0], [57.5, 4055.0], [57.6, 4058.0], [57.7, 4061.0], [57.8, 4062.0], [57.9, 4064.0], [58.0, 4068.0], [58.1, 4077.0], [58.2, 4085.0], [58.3, 4087.0], [58.4, 4090.0], [58.5, 4093.0], [58.6, 4099.0], [58.7, 4102.0], [58.8, 4111.0], [58.9, 4116.0], [59.0, 4118.0], [59.1, 4121.0], [59.2, 4122.0], [59.3, 4125.0], [59.4, 4128.0], [59.5, 4135.0], [59.6, 4137.0], [59.7, 4140.0], [59.8, 4144.0], [59.9, 4150.0], [60.0, 4157.0], [60.1, 4160.0], [60.2, 4161.0], [60.3, 4166.0], [60.4, 4169.0], [60.5, 4171.0], [60.6, 4175.0], [60.7, 4176.0], [60.8, 4181.0], [60.9, 4182.0], [61.0, 4185.0], [61.1, 4186.0], [61.2, 4189.0], [61.3, 4196.0], [61.4, 4197.0], [61.5, 4200.0], [61.6, 4209.0], [61.7, 4214.0], [61.8, 4216.0], [61.9, 4220.0], [62.0, 4226.0], [62.1, 4232.0], [62.2, 4235.0], [62.3, 4236.0], [62.4, 4241.0], [62.5, 4245.0], [62.6, 4246.0], [62.7, 4248.0], [62.8, 4251.0], [62.9, 4255.0], [63.0, 4257.0], [63.1, 4262.0], [63.2, 4265.0], [63.3, 4270.0], [63.4, 4275.0], [63.5, 4282.0], [63.6, 4287.0], [63.7, 4291.0], [63.8, 4293.0], [63.9, 4296.0], [64.0, 4298.0], [64.1, 4302.0], [64.2, 4306.0], [64.3, 4314.0], [64.4, 4317.0], [64.5, 4325.0], [64.6, 4329.0], [64.7, 4335.0], [64.8, 4341.0], [64.9, 4343.0], [65.0, 4348.0], [65.1, 4354.0], [65.2, 4358.0], [65.3, 4361.0], [65.4, 4366.0], [65.5, 4369.0], [65.6, 4376.0], [65.7, 4380.0], [65.8, 4383.0], [65.9, 4384.0], [66.0, 4387.0], [66.1, 4389.0], [66.2, 4392.0], [66.3, 4393.0], [66.4, 4396.0], [66.5, 4405.0], [66.6, 4406.0], [66.7, 4409.0], [66.8, 4413.0], [66.9, 4419.0], [67.0, 4422.0], [67.1, 4431.0], [67.2, 4434.0], [67.3, 4438.0], [67.4, 4439.0], [67.5, 4440.0], [67.6, 4447.0], [67.7, 4451.0], [67.8, 4456.0], [67.9, 4462.0], [68.0, 4465.0], [68.1, 4473.0], [68.2, 4475.0], [68.3, 4478.0], [68.4, 4479.0], [68.5, 4486.0], [68.6, 4491.0], [68.7, 4494.0], [68.8, 4502.0], [68.9, 4505.0], [69.0, 4507.0], [69.1, 4516.0], [69.2, 4517.0], [69.3, 4521.0], [69.4, 4522.0], [69.5, 4532.0], [69.6, 4536.0], [69.7, 4541.0], [69.8, 4546.0], [69.9, 4551.0], [70.0, 4554.0], [70.1, 4557.0], [70.2, 4564.0], [70.3, 4566.0], [70.4, 4572.0], [70.5, 4577.0], [70.6, 4584.0], [70.7, 4589.0], [70.8, 4592.0], [70.9, 4597.0], [71.0, 4598.0], [71.1, 4601.0], [71.2, 4605.0], [71.3, 4607.0], [71.4, 4611.0], [71.5, 4620.0], [71.6, 4625.0], [71.7, 4626.0], [71.8, 4631.0], [71.9, 4632.0], [72.0, 4641.0], [72.1, 4650.0], [72.2, 4653.0], [72.3, 4655.0], [72.4, 4667.0], [72.5, 4668.0], [72.6, 4672.0], [72.7, 4675.0], [72.8, 4680.0], [72.9, 4682.0], [73.0, 4689.0], [73.1, 4701.0], [73.2, 4703.0], [73.3, 4707.0], [73.4, 4714.0], [73.5, 4717.0], [73.6, 4719.0], [73.7, 4729.0], [73.8, 4734.0], [73.9, 4738.0], [74.0, 4743.0], [74.1, 4748.0], [74.2, 4765.0], [74.3, 4772.0], [74.4, 4778.0], [74.5, 4781.0], [74.6, 4784.0], [74.7, 4789.0], [74.8, 4791.0], [74.9, 4799.0], [75.0, 4801.0], [75.1, 4805.0], [75.2, 4810.0], [75.3, 4813.0], [75.4, 4822.0], [75.5, 4824.0], [75.6, 4828.0], [75.7, 4830.0], [75.8, 4836.0], [75.9, 4839.0], [76.0, 4843.0], [76.1, 4847.0], [76.2, 4853.0], [76.3, 4860.0], [76.4, 4863.0], [76.5, 4874.0], [76.6, 4885.0], [76.7, 4892.0], [76.8, 4900.0], [76.9, 4903.0], [77.0, 4906.0], [77.1, 4913.0], [77.2, 4918.0], [77.3, 4924.0], [77.4, 4929.0], [77.5, 4938.0], [77.6, 4943.0], [77.7, 4951.0], [77.8, 4959.0], [77.9, 4961.0], [78.0, 4972.0], [78.1, 4974.0], [78.2, 4980.0], [78.3, 4988.0], [78.4, 4994.0], [78.5, 5001.0], [78.6, 5004.0], [78.7, 5007.0], [78.8, 5012.0], [78.9, 5014.0], [79.0, 5021.0], [79.1, 5025.0], [79.2, 5034.0], [79.3, 5037.0], [79.4, 5049.0], [79.5, 5051.0], [79.6, 5057.0], [79.7, 5064.0], [79.8, 5074.0], [79.9, 5080.0], [80.0, 5093.0], [80.1, 5107.0], [80.2, 5112.0], [80.3, 5116.0], [80.4, 5132.0], [80.5, 5141.0], [80.6, 5147.0], [80.7, 5152.0], [80.8, 5157.0], [80.9, 5160.0], [81.0, 5170.0], [81.1, 5184.0], [81.2, 5185.0], [81.3, 5194.0], [81.4, 5198.0], [81.5, 5202.0], [81.6, 5211.0], [81.7, 5214.0], [81.8, 5222.0], [81.9, 5227.0], [82.0, 5231.0], [82.1, 5237.0], [82.2, 5243.0], [82.3, 5261.0], [82.4, 5264.0], [82.5, 5279.0], [82.6, 5286.0], [82.7, 5300.0], [82.8, 5306.0], [82.9, 5311.0], [83.0, 5319.0], [83.1, 5329.0], [83.2, 5340.0], [83.3, 5347.0], [83.4, 5352.0], [83.5, 5355.0], [83.6, 5361.0], [83.7, 5371.0], [83.8, 5379.0], [83.9, 5390.0], [84.0, 5401.0], [84.1, 5415.0], [84.2, 5422.0], [84.3, 5428.0], [84.4, 5436.0], [84.5, 5442.0], [84.6, 5447.0], [84.7, 5453.0], [84.8, 5461.0], [84.9, 5474.0], [85.0, 5499.0], [85.1, 5511.0], [85.2, 5514.0], [85.3, 5525.0], [85.4, 5529.0], [85.5, 5543.0], [85.6, 5555.0], [85.7, 5563.0], [85.8, 5572.0], [85.9, 5577.0], [86.0, 5582.0], [86.1, 5583.0], [86.2, 5592.0], [86.3, 5598.0], [86.4, 5602.0], [86.5, 5607.0], [86.6, 5616.0], [86.7, 5621.0], [86.8, 5636.0], [86.9, 5648.0], [87.0, 5675.0], [87.1, 5681.0], [87.2, 5688.0], [87.3, 5691.0], [87.4, 5720.0], [87.5, 5731.0], [87.6, 5738.0], [87.7, 5748.0], [87.8, 5755.0], [87.9, 5771.0], [88.0, 5783.0], [88.1, 5796.0], [88.2, 5809.0], [88.3, 5815.0], [88.4, 5834.0], [88.5, 5836.0], [88.6, 5862.0], [88.7, 5868.0], [88.8, 5874.0], [88.9, 5893.0], [89.0, 5897.0], [89.1, 5911.0], [89.2, 5920.0], [89.3, 5948.0], [89.4, 5969.0], [89.5, 5976.0], [89.6, 5996.0], [89.7, 6006.0], [89.8, 6013.0], [89.9, 6024.0], [90.0, 6046.0], [90.1, 6059.0], [90.2, 6066.0], [90.3, 6078.0], [90.4, 6091.0], [90.5, 6118.0], [90.6, 6126.0], [90.7, 6138.0], [90.8, 6178.0], [90.9, 6201.0], [91.0, 6208.0], [91.1, 6234.0], [91.2, 6258.0], [91.3, 6283.0], [91.4, 6309.0], [91.5, 6334.0], [91.6, 6357.0], [91.7, 6382.0], [91.8, 6391.0], [91.9, 6412.0], [92.0, 6416.0], [92.1, 6427.0], [92.2, 6440.0], [92.3, 6457.0], [92.4, 6475.0], [92.5, 6499.0], [92.6, 6525.0], [92.7, 6547.0], [92.8, 6563.0], [92.9, 6568.0], [93.0, 6574.0], [93.1, 6585.0], [93.2, 6599.0], [93.3, 6637.0], [93.4, 6650.0], [93.5, 6703.0], [93.6, 6712.0], [93.7, 6721.0], [93.8, 6749.0], [93.9, 6784.0], [94.0, 6821.0], [94.1, 6868.0], [94.2, 6878.0], [94.3, 6906.0], [94.4, 6919.0], [94.5, 6948.0], [94.6, 6973.0], [94.7, 7044.0], [94.8, 7073.0], [94.9, 7140.0], [95.0, 7204.0], [95.1, 7219.0], [95.2, 7353.0], [95.3, 7405.0], [95.4, 7464.0], [95.5, 7515.0], [95.6, 7572.0], [95.7, 7584.0], [95.8, 7602.0], [95.9, 7718.0], [96.0, 7743.0], [96.1, 7854.0], [96.2, 7913.0], [96.3, 8033.0], [96.4, 8089.0], [96.5, 8145.0], [96.6, 8310.0], [96.7, 8405.0], [96.8, 8479.0], [96.9, 8492.0], [97.0, 8547.0], [97.1, 8654.0], [97.2, 8815.0], [97.3, 8901.0], [97.4, 9092.0], [97.5, 9142.0], [97.6, 9277.0], [97.7, 9406.0], [97.8, 9557.0], [97.9, 9721.0], [98.0, 9746.0], [98.1, 9939.0], [98.2, 10195.0], [98.3, 10424.0], [98.4, 10484.0], [98.5, 10830.0], [98.6, 11124.0], [98.7, 11247.0], [98.8, 11877.0], [98.9, 12126.0], [99.0, 12855.0], [99.1, 13321.0], [99.2, 14010.0], [99.3, 14625.0], [99.4, 14869.0], [99.5, 16189.0], [99.6, 16674.0], [99.7, 20008.0], [99.8, 26601.0], [99.9, 35529.0], [100.0, 60019.0]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "maxX": 100.0, "title": "Response Time Percentiles"}},
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
        data: {"result": {"minY": 1.0, "minX": 600.0, "maxY": 123.0, "series": [{"data": [[33100.0, 2.0], [35500.0, 1.0], [46300.0, 1.0], [600.0, 1.0], [800.0, 2.0], [900.0, 6.0], [1000.0, 5.0], [1100.0, 11.0], [1200.0, 12.0], [1300.0, 20.0], [1400.0, 17.0], [1500.0, 16.0], [1600.0, 15.0], [1700.0, 22.0], [1800.0, 30.0], [1900.0, 31.0], [2000.0, 29.0], [2100.0, 51.0], [2300.0, 56.0], [2200.0, 55.0], [2400.0, 65.0], [2500.0, 77.0], [2600.0, 63.0], [2700.0, 95.0], [2800.0, 105.0], [2900.0, 109.0], [3000.0, 111.0], [3100.0, 100.0], [3200.0, 112.0], [3300.0, 121.0], [3400.0, 115.0], [3500.0, 116.0], [3600.0, 115.0], [3700.0, 123.0], [3800.0, 98.0], [3900.0, 98.0], [4000.0, 95.0], [4200.0, 92.0], [4300.0, 85.0], [4100.0, 101.0], [4400.0, 85.0], [4500.0, 80.0], [4600.0, 73.0], [4700.0, 66.0], [4800.0, 67.0], [5100.0, 49.0], [4900.0, 61.0], [5000.0, 55.0], [5200.0, 45.0], [5300.0, 47.0], [5500.0, 47.0], [5400.0, 37.0], [5600.0, 35.0], [5700.0, 29.0], [5800.0, 32.0], [6000.0, 27.0], [6100.0, 16.0], [5900.0, 23.0], [6300.0, 16.0], [6200.0, 19.0], [6400.0, 24.0], [6600.0, 10.0], [6500.0, 25.0], [6700.0, 16.0], [6900.0, 14.0], [6800.0, 12.0], [7000.0, 6.0], [7100.0, 6.0], [7400.0, 8.0], [7200.0, 7.0], [7300.0, 3.0], [7500.0, 10.0], [7600.0, 3.0], [7800.0, 4.0], [7900.0, 4.0], [7700.0, 7.0], [8000.0, 5.0], [8100.0, 5.0], [8300.0, 3.0], [8500.0, 4.0], [8600.0, 3.0], [8400.0, 10.0], [8700.0, 2.0], [8900.0, 3.0], [9100.0, 4.0], [9000.0, 2.0], [9200.0, 4.0], [8800.0, 4.0], [9600.0, 2.0], [9700.0, 5.0], [9500.0, 2.0], [9400.0, 3.0], [9300.0, 1.0], [10200.0, 1.0], [10100.0, 3.0], [9800.0, 3.0], [9900.0, 1.0], [10300.0, 2.0], [10400.0, 4.0], [10500.0, 2.0], [10600.0, 1.0], [11200.0, 4.0], [11000.0, 2.0], [11100.0, 2.0], [10800.0, 1.0], [11400.0, 2.0], [12000.0, 1.0], [11900.0, 1.0], [11800.0, 1.0], [12100.0, 1.0], [12500.0, 1.0], [12400.0, 1.0], [13300.0, 2.0], [13000.0, 1.0], [12800.0, 2.0], [13100.0, 1.0], [13900.0, 1.0], [14000.0, 2.0], [14300.0, 1.0], [14700.0, 1.0], [14800.0, 3.0], [14600.0, 1.0], [14400.0, 1.0], [14900.0, 1.0], [15600.0, 1.0], [16100.0, 1.0], [15900.0, 1.0], [16500.0, 1.0], [16600.0, 1.0], [16400.0, 1.0], [18300.0, 1.0], [18000.0, 1.0], [19400.0, 1.0], [20000.0, 1.0], [24500.0, 1.0], [26200.0, 1.0], [26600.0, 1.0], [32600.0, 1.0], [60000.0, 2.0]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 100, "maxX": 60000.0, "title": "Response Time Distribution"}},
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
        data: {"result": {"minY": 69.0, "minX": 1.0, "ticks": [[0, "Requests having \nresponse time <= 500ms"], [1, "Requests having \nresponse time > 500ms and <= 1,500ms"], [2, "Requests having \nresponse time > 1,500ms"], [3, "Requests in error"]], "maxY": 3425.0, "series": [{"data": [], "color": "#9ACD32", "isOverall": false, "label": "Requests having \nresponse time <= 500ms", "isController": false}, {"data": [[1.0, 69.0]], "color": "yellow", "isOverall": false, "label": "Requests having \nresponse time > 500ms and <= 1,500ms", "isController": false}, {"data": [[2.0, 3425.0]], "color": "orange", "isOverall": false, "label": "Requests having \nresponse time > 1,500ms", "isController": false}, {"data": [[3.0, 82.0]], "color": "#FF6347", "isOverall": false, "label": "Requests in error", "isController": false}], "supportsControllersDiscrimination": false, "maxX": 3.0, "title": "Synthetic Response Times Distribution"}},
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
        data: {"result": {"minY": 46.778947368421036, "minX": 1.79103426E12, "maxY": 50.0, "series": [{"data": [[1.79103444E12, 50.0], [1.79103456E12, 46.778947368421036], [1.79103426E12, 50.0], [1.79103438E12, 50.0], [1.79103432E12, 50.0], [1.7910345E12, 50.0]], "isOverall": false, "label": "Authenticated users", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103456E12, "title": "Active Threads Over Time"}},
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
        data: {"result": {"minY": 1184.0, "minX": 1.0, "maxY": 46326.0, "series": [{"data": [[2.0, 46326.0], [3.0, 26601.0], [4.0, 4611.0], [5.0, 8815.0], [6.0, 5835.0], [7.0, 6204.0], [8.0, 24575.0], [9.0, 33101.0], [10.0, 10192.0], [11.0, 35529.0], [12.0, 32671.0], [13.0, 2177.0], [14.0, 2806.0], [15.0, 2256.0], [16.0, 2434.0], [17.0, 3288.0], [18.0, 2891.0], [19.0, 3339.0], [20.0, 5270.0], [22.0, 3666.0], [23.0, 3506.0], [24.0, 1816.0], [25.0, 1567.0], [26.0, 3091.0], [27.0, 3560.0], [28.0, 3220.0], [29.0, 3491.0], [30.0, 3539.0], [31.0, 2797.0], [33.0, 4247.0], [32.0, 1732.0], [35.0, 5316.0], [34.0, 33192.0], [37.0, 4863.0], [36.0, 2888.0], [39.0, 2934.0], [38.0, 2807.0], [41.0, 3214.0], [40.0, 2717.0], [43.0, 3890.0], [42.0, 1184.0], [45.0, 2930.0], [44.0, 2340.0], [47.0, 3698.0], [46.0, 2657.0], [49.0, 2498.0], [48.0, 5895.0], [50.0, 4155.797278140063], [1.0, 8089.0]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}, {"data": [[49.65771812080536, 4207.893736017896]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-Aggregated", "isController": false}], "supportsControllersDiscrimination": true, "maxX": 50.0, "title": "Time VS Threads"}},
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
        data : {"result": {"minY": 3672.0, "minX": 1.79103426E12, "maxY": 3879852.8, "series": [{"data": [[1.79103444E12, 3506768.65], [1.79103456E12, 1974246.4833333334], [1.79103426E12, 1251210.7833333334], [1.79103438E12, 3879852.8], [1.79103432E12, 3344025.7666666666], [1.7910345E12, 3772617.9]], "isOverall": false, "label": "Bytes received per second", "isController": false}, {"data": [[1.79103444E12, 10252.8], [1.79103456E12, 5472.0], [1.79103426E12, 3672.0], [1.79103438E12, 11419.2], [1.79103432E12, 9648.0], [1.7910345E12, 11001.6]], "isOverall": false, "label": "Bytes sent per second", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103456E12, "title": "Bytes Throughput Over Time"}},
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
        data: {"result": {"minY": 3804.361916771754, "minX": 1.79103426E12, "maxY": 5200.172549019609, "series": [{"data": [[1.79103444E12, 4156.359550561801], [1.79103456E12, 4456.571052631582], [1.79103426E12, 5200.172549019609], [1.79103438E12, 3804.361916771754], [1.79103432E12, 4590.518628912072], [1.7910345E12, 3884.264052287582]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103456E12, "title": "Response Time Over Time"}},
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
        data: {"result": {"minY": 3800.50441361917, "minX": 1.79103426E12, "maxY": 5189.247058823529, "series": [{"data": [[1.79103444E12, 4150.44662921348], [1.79103456E12, 4445.413157894736], [1.79103426E12, 5189.247058823529], [1.79103438E12, 3800.50441361917], [1.79103432E12, 4496.351713859914], [1.7910345E12, 3801.1790849673216]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103456E12, "title": "Latencies Over Time"}},
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
        data: {"result": {"minY": 0.12610340479192933, "minX": 1.79103426E12, "maxY": 1.5058823529411753, "series": [{"data": [[1.79103444E12, 0.14185393258426965], [1.79103456E12, 0.13157894736842118], [1.79103426E12, 1.5058823529411753], [1.79103438E12, 0.12610340479192933], [1.79103432E12, 0.19076005961251868], [1.7910345E12, 0.13333333333333336]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103456E12, "title": "Connect Time Over Time"}},
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
        data: {"result": {"minY": 885.0, "minX": 1.79103426E12, "maxY": 20008.0, "series": [{"data": [[1.79103444E12, 9721.0], [1.79103456E12, 10192.0], [1.79103426E12, 18381.0], [1.79103438E12, 13112.0], [1.79103432E12, 20008.0], [1.7910345E12, 11037.0]], "isOverall": false, "label": "Max", "isController": false}, {"data": [[1.79103444E12, 1008.0], [1.79103456E12, 1184.0], [1.79103426E12, 916.0], [1.79103438E12, 1005.0], [1.79103432E12, 885.0], [1.7910345E12, 1189.0]], "isOverall": false, "label": "Min", "isController": false}, {"data": [[1.79103444E12, 6044.0], [1.79103456E12, 5878.2], [1.79103426E12, 8953.0], [1.79103438E12, 5371.0], [1.79103432E12, 6928.0], [1.7910345E12, 5363.8]], "isOverall": false, "label": "90th percentile", "isController": false}, {"data": [[1.79103444E12, 8193.16], [1.79103456E12, 8965.079999999996], [1.79103426E12, 17381.0], [1.79103438E12, 9426.499999999969], [1.79103432E12, 12845.85], [1.7910345E12, 8316.320000000005]], "isOverall": false, "label": "99th percentile", "isController": false}, {"data": [[1.79103444E12, 4027.5], [1.79103456E12, 3619.0], [1.79103426E12, 4405.0], [1.79103438E12, 3620.0], [1.79103432E12, 4027.5], [1.7910345E12, 3681.0]], "isOverall": false, "label": "Median", "isController": false}, {"data": [[1.79103444E12, 6673.1], [1.79103456E12, 6680.399999999998], [1.79103426E12, 14180.0], [1.79103438E12, 6064.5], [1.79103432E12, 9471.549999999996], [1.7910345E12, 6061.4]], "isOverall": false, "label": "95th percentile", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103456E12, "title": "Response Time Percentiles Over Time (successful requests only)"}},
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
    data: {"result": {"minY": 2735.5, "minX": 1.0, "maxY": 33192.0, "series": [{"data": [[37.0, 3462.0], [3.0, 6246.0], [4.0, 4704.5], [5.0, 4218.0], [6.0, 3709.0], [7.0, 3866.0], [8.0, 3804.5], [9.0, 3473.5], [10.0, 3743.5], [11.0, 3775.5], [12.0, 3916.0], [13.0, 3858.5], [14.0, 3686.0], [15.0, 3966.0], [16.0, 3773.0], [1.0, 3988.0], [17.0, 3827.0], [18.0, 3698.0], [19.0, 3764.0], [20.0, 3577.0], [21.0, 4697.5], [22.0, 3774.0], [23.0, 3761.0], [24.0, 4370.5], [26.0, 4251.0]], "isOverall": false, "label": "Successes", "isController": false}, {"data": [[8.0, 3786.5], [9.0, 3576.0], [10.0, 3209.0], [11.0, 3955.0], [12.0, 2894.0], [13.0, 2951.0], [14.0, 2735.5], [15.0, 32671.0], [16.0, 3161.0], [1.0, 8089.0], [17.0, 3135.5], [18.0, 3243.0], [19.0, 3201.0], [5.0, 5157.0], [20.0, 33192.0], [21.0, 2935.0], [23.0, 2958.0], [6.0, 2981.0], [7.0, 24575.0]], "isOverall": false, "label": "Failures", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 1000, "maxX": 37.0, "title": "Response Time Vs Request"}},
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
    data: {"result": {"minY": 2432.0, "minX": 1.0, "maxY": 33045.0, "series": [{"data": [[37.0, 3457.0], [3.0, 6237.0], [4.0, 4698.5], [5.0, 4213.5], [6.0, 3705.0], [7.0, 3851.0], [8.0, 3801.0], [9.0, 3470.0], [10.0, 3740.5], [11.0, 3772.0], [12.0, 3912.0], [13.0, 3855.0], [14.0, 3682.5], [15.0, 3962.5], [16.0, 3769.5], [1.0, 3984.0], [17.0, 3824.0], [18.0, 3694.0], [19.0, 3760.0], [20.0, 3574.0], [21.0, 4693.5], [22.0, 3770.5], [23.0, 3757.0], [24.0, 4366.5], [26.0, 4244.5]], "isOverall": false, "label": "Successes", "isController": false}, {"data": [[8.0, 3729.5], [9.0, 3561.0], [10.0, 2885.0], [11.0, 3950.5], [12.0, 2563.0], [13.0, 2432.0], [14.0, 2695.5], [15.0, 32618.0], [16.0, 3146.5], [1.0, 8081.0], [17.0, 3065.0], [18.0, 3237.0], [19.0, 3195.0], [5.0, 5147.0], [20.0, 33045.0], [21.0, 2593.0], [23.0, 2802.0], [6.0, 2937.0], [7.0, 24551.0]], "isOverall": false, "label": "Failures", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 1000, "maxX": 37.0, "title": "Latencies Vs Request"}},
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
        data: {"result": {"minY": 5.083333333333333, "minX": 1.79103426E12, "maxY": 13.216666666666667, "series": [{"data": [[1.79103444E12, 11.85], [1.79103456E12, 5.5], [1.79103426E12, 5.083333333333333], [1.79103438E12, 13.216666666666667], [1.79103432E12, 11.183333333333334], [1.7910345E12, 12.766666666666667]], "isOverall": false, "label": "hitsPerSecond", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103456E12, "title": "Hits Per Second"}},
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
        data: {"result": {"minY": 0.016666666666666666, "minX": 1.79103426E12, "maxY": 12.816666666666666, "series": [{"data": [[1.79103444E12, 11.633333333333333], [1.79103456E12, 6.116666666666666], [1.79103426E12, 4.15], [1.79103438E12, 12.816666666666666], [1.79103432E12, 11.0], [1.7910345E12, 12.516666666666667]], "isOverall": false, "label": "200", "isController": false}, {"data": [[1.79103432E12, 0.016666666666666666], [1.7910345E12, 0.016666666666666666]], "isOverall": false, "label": "Non HTTP response code: java.net.SocketTimeoutException", "isController": false}, {"data": [[1.79103444E12, 0.23333333333333334], [1.79103456E12, 0.21666666666666667], [1.79103426E12, 0.1], [1.79103438E12, 0.4], [1.79103432E12, 0.16666666666666666], [1.7910345E12, 0.21666666666666667]], "isOverall": false, "label": "500", "isController": false}], "supportsControllersDiscrimination": false, "granularity": 60000, "maxX": 1.79103456E12, "title": "Codes Per Second"}},
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
        data: {"result": {"minY": 0.1, "minX": 1.79103426E12, "maxY": 12.816666666666666, "series": [{"data": [[1.79103444E12, 11.633333333333333], [1.79103456E12, 6.116666666666666], [1.79103426E12, 4.15], [1.79103438E12, 12.816666666666666], [1.79103432E12, 11.0], [1.7910345E12, 12.516666666666667]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-success", "isController": false}, {"data": [[1.79103444E12, 0.23333333333333334], [1.79103456E12, 0.21666666666666667], [1.79103426E12, 0.1], [1.79103438E12, 0.4], [1.79103432E12, 0.18333333333333332], [1.7910345E12, 0.23333333333333334]], "isOverall": false, "label": "MEASURED - Procurement dashboard FY2026-failure", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103456E12, "title": "Transactions Per Second"}},
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
        data: {"result": {"minY": 0.1, "minX": 1.79103426E12, "maxY": 12.816666666666666, "series": [{"data": [[1.79103444E12, 11.633333333333333], [1.79103456E12, 6.116666666666666], [1.79103426E12, 4.15], [1.79103438E12, 12.816666666666666], [1.79103432E12, 11.0], [1.7910345E12, 12.516666666666667]], "isOverall": false, "label": "Transaction-success", "isController": false}, {"data": [[1.79103444E12, 0.23333333333333334], [1.79103456E12, 0.21666666666666667], [1.79103426E12, 0.1], [1.79103438E12, 0.4], [1.79103432E12, 0.18333333333333332], [1.7910345E12, 0.23333333333333334]], "isOverall": false, "label": "Transaction-failure", "isController": false}], "supportsControllersDiscrimination": true, "granularity": 60000, "maxX": 1.79103456E12, "title": "Total Transactions Per Second"}},
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

