<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LocationsSeeder extends Seeder
{
    protected $importFullDataset = false;
    protected $sqlDumpPath = 'locations.sql';

    public function run(): void
    {
        $this->clearExistingData();
        $this->seedCounties();
        $this->seedConstituencies();
        $this->importFullDataset ? $this->seedFullWardsFromSql() : $this->seedSampleWards();
        $this->printSummary();
    }

    private function clearExistingData(): void
    {
        // Truncate tables in a driver-prognostic way. 
        // MongoDB doesn't have foreign key checks to disable.
        if (DB::getDriverName() !== 'mongodb') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        DB::table('location_area')->truncate();
        DB::table('constituencies')->truncate();
        DB::table('location')->truncate();
        
        if (DB::getDriverName() !== 'mongodb') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function seedCounties(): void
    {
        $counties = [
            [1, 'Mombasa'], [2, 'Kwale'], [3, 'Kilifi'], [4, 'Tana River'], [5, 'Lamu'],
            [6, 'Taita Taveta'], [7, 'Garissa'], [8, 'Wajir'], [9, 'Mandera'], [10, 'Marsabit'],
            [11, 'Isiolo'], [12, 'Meru'], [13, 'Tharaka Nithi'], [14, 'Embu'], [15, 'Kitui'],
            [16, 'Machakos'], [17, 'Makueni'], [18, 'Nyandarua'], [19, 'Nyeri'], [20, 'Kirinyaga'],
            [21, "Murang'a"], [22, 'Kiambu'], [23, 'Turkana'], [24, 'West Pokot'], [25, 'Samburu'],
            [26, 'Trans Nzoia'], [27, 'Uasin Gishu'], [28, 'Elgeyo Marakwet'], [29, 'Nandi'], [30, 'Baringo'],
            [31, 'Laikipia'], [32, 'Nakuru'], [33, 'Narok'], [34, 'Kajiado'], [35, 'Kericho'],
            [36, 'Bomet'], [37, 'Kakamega'], [38, 'Vihiga'], [39, 'Bungoma'], [40, 'Busia'],
            [41, 'Siaya'], [42, 'Kisumu'], [43, 'Homa Bay'], [44, 'Migori'], [45, 'Kisii'],
            [46, 'Nyamira'], [47, 'Nairobi City']
        ];

        $insertData = [];
        foreach ($counties as [$id, $name]) {
            $insertData[] = ['id' => $id, 'description' => $name, 'status' => 1];
        }

        DB::table('location')->insert($insertData);
    }

    private function seedConstituencies(): void
    {
        $constituencies = [
            // Mombasa (1-6)
            [1, 1, 'Changamwe'], [2, 1, 'Jomvu'], [3, 1, 'Kisauni'], [4, 1, 'Nyali'], [5, 1, 'Likoni'], [6, 1, 'Mvita'],
            // Kwale (7-10)
            [7, 2, 'Msambweni'], [8, 2, 'Lunga Lunga'], [9, 2, 'Matuga'], [10, 2, 'Kinango'],
            // Kilifi (11-17)
            [11, 3, 'Kilifi North'], [12, 3, 'Kilifi South'], [13, 3, 'Kaloleni'], [14, 3, 'Rabai'], [15, 3, 'Ganze'], [16, 3, 'Malindi'], [17, 3, 'Magarini'],
            // Tana River (18-20)
            [18, 4, 'Garsen'], [19, 4, 'Galole'], [20, 4, 'Bura'],
            // Lamu (21-22)
            [21, 5, 'Lamu East'], [22, 5, 'Lamu West'],
            // Taita Taveta (23-26)
            [23, 6, 'Taveta'], [24, 6, 'Wundanyi'], [25, 6, 'Mwatate'], [26, 6, 'Voi'],
            // Garissa (27-32)
            [27, 7, 'Garissa Township'], [28, 7, 'Balambala'], [29, 7, 'Lagdera'], [30, 7, 'Dadaab'], [31, 7, 'Fafi'], [32, 7, 'Ijara'],
            // Wajir (33-38)
            [33, 8, 'Wajir East'], [34, 8, 'Tarbaj'], [35, 8, 'Wajir West'], [36, 8, 'Eldas'], [37, 8, 'Wajir South'], [38, 8, 'Wajir North'],
            // Mandera (39-44)
            [39, 9, 'Mandera West'], [40, 9, 'Banissa'], [41, 9, 'Mandera North'], [42, 9, 'Mandera East'], [43, 9, 'Lafey'], [44, 9, 'Mandera South'],
            // Marsabit (45-48)
            [45, 10, 'Moyale'], [46, 10, 'North Horr'], [47, 10, 'Saku'], [48, 10, 'Laisamis'],
            // Isiolo (49-50)
            [49, 11, 'Isiolo North'], [50, 11, 'Isiolo South'],
            // Meru (51-59)
            [51, 12, 'Igembe South'], [52, 12, 'Igembe Central'], [53, 12, 'Igembe North'], [54, 12, 'Tigania West'], [55, 12, 'Tigania East'],
            [56, 12, 'North Imenti'], [57, 12, 'Buuri'], [58, 12, 'Central Imenti'], [59, 12, 'South Imenti'],
            // Tharaka Nithi (60-62)
            [60, 13, 'Maara'], [61, 13, "Chuka/Igambang'ombe"], [62, 13, 'Tharaka'],
            // Embu (63-66)
            [63, 14, 'Manyatta'], [64, 14, 'Runyenjes'], [65, 14, 'Mbeere South'], [66, 14, 'Mbeere North'],
            // Kitui (67-74)
            [67, 15, 'Mwingi North'], [68, 15, 'Mwingi West'], [69, 15, 'Mwingi Central'], [70, 15, 'Kitui West'],
            [71, 15, 'Kitui Rural'], [72, 15, 'Kitui Central'], [73, 15, 'Kitui East'], [74, 15, 'Kitui South'],
            // Machakos (75-82)
            [75, 16, 'Masinga'], [76, 16, 'Yatta'], [77, 16, 'Kangundo'], [78, 16, 'Matungulu'],
            [79, 16, 'Kathiani'], [80, 16, 'Mavoko'], [81, 16, 'Machakos Town'], [82, 16, 'Mwala'],
            // Makueni (83-88)
            [83, 17, 'Mbooni'], [84, 17, 'Kilome'], [85, 17, 'Kaiti'], [86, 17, 'Makueni'], [87, 17, 'Kibwezi West'], [88, 17, 'Kibwezi East'],
            // Nyandarua (89-93)
            [89, 18, 'Kinangop'], [90, 18, 'Kipipiri'], [91, 18, 'Ol Kalou'], [92, 18, 'Ol Jorok'], [93, 18, 'Ndaragwa'],
            // Nyeri (94-99)
            [94, 19, 'Tetu'], [95, 19, 'Kieni'], [96, 19, 'Mathira'], [97, 19, 'Othaya'], [98, 19, 'Mukurweini'], [99, 19, 'Nyeri Town'],
            // Kirinyaga (100-103)
            [100, 20, 'Mwea'], [101, 20, 'Gichugu'], [102, 20, 'Ndia'], [103, 20, 'Kirinyaga Central'],
            // Muranga (104-110)
            [104, 21, 'Kangema'], [105, 21, 'Mathioya'], [106, 21, 'Kiharu'], [107, 21, 'Kigumo'], [108, 21, 'Maragua'], [109, 21, 'Kandara'], [110, 21, 'Gatanga'],
            // Kiambu (111-122)
            [111, 22, 'Gatundu South'], [112, 22, 'Gatundu North'], [113, 22, 'Juja'], [114, 22, 'Thika Town'],
            [115, 22, 'Ruiru'], [116, 22, 'Githunguri'], [117, 22, 'Kiambu Town'], [118, 22, 'Kiambaa'],
            [119, 22, 'Kabete'], [120, 22, 'Kikuyu'], [121, 22, 'Limuru'], [122, 22, 'Lari'],
            // Turkana (123-128)
            [123, 23, 'Turkana North'], [124, 23, 'Turkana West'], [125, 23, 'Turkana Central'], [126, 23, 'Loima'], [127, 23, 'Turkana South'], [128, 23, 'Turkana East'],
            // West Pokot (129-132)
            [129, 24, 'Kapenguria'], [130, 24, 'Sigor'], [131, 24, 'Kacheliba'], [132, 24, 'Pokot South'],
            // Samburu (133-135)
            [133, 25, 'Samburu West'], [134, 25, 'Samburu North'], [135, 25, 'Samburu East'],
            // Trans Nzoia (136-140)
            [136, 26, 'Kwanza'], [137, 26, 'Endebess'], [138, 26, 'Saboti'], [139, 26, 'Kiminini'], [140, 26, 'Cherangany'],
            // Uasin Gishu (141-146)
            [141, 27, 'Soy'], [142, 27, 'Turbo'], [143, 27, 'Moiben'], [144, 27, 'Ainabkoi'], [145, 27, 'Kapseret'], [146, 27, 'Kesses'],
            // Elgeyo Marakwet (147-150)
            [147, 28, 'Marakwet East'], [148, 28, 'Marakwet West'], [149, 28, 'Keiyo North'], [150, 28, 'Keiyo South'],
            // Nandi (151-156)
            [151, 29, 'Tinderet'], [152, 29, 'Aldai'], [153, 29, 'Nandi Hills'], [154, 29, 'Chesumei'], [155, 29, 'Emgwen'], [156, 29, 'Mosop'],
            // Baringo (157-162)
            [157, 30, 'Tiaty'], [158, 30, 'Baringo North'], [159, 30, 'Baringo Central'], [160, 30, 'Baringo South'], [161, 30, 'Mogotio'], [162, 30, 'Eldama Ravine'],
            // Laikipia (163-165)
            [163, 31, 'Laikipia West'], [164, 31, 'Laikipia East'], [165, 31, 'Laikipia North'],
            // Nakuru (166-176)
            [166, 32, 'Molo'], [167, 32, 'Njoro'], [168, 32, 'Naivasha'], [169, 32, 'Gilgil'], [170, 32, 'Kuresoi South'],
            [171, 32, 'Kuresoi North'], [172, 32, 'Subukia'], [173, 32, 'Rongai'], [174, 32, 'Bahati'], [175, 32, 'Nakuru Town West'], [176, 32, 'Nakuru Town East'],
            // Narok (177-182)
            [177, 33, 'Kilgoris'], [178, 33, 'Emurua Dikirr'], [179, 33, 'Narok North'], [180, 33, 'Narok East'], [181, 33, 'Narok South'], [182, 33, 'Narok West'],
            // Kajiado (183-187)
            [183, 34, 'Kajiado North'], [184, 34, 'Kajiado Central'], [185, 34, 'Kajiado East'], [186, 34, 'Kajiado West'], [187, 34, 'Kajiado South'],
            // Kericho (188-193)
            [188, 35, 'Ainamoi'], [189, 35, 'Belgut'], [190, 35, 'Kipkelion East'], [191, 35, 'Kipkelion West'], [192, 35, 'Bureti'], [193, 35, 'Soin/Sigowet'],
            // Bomet (194-198)
            [194, 36, 'Sotik'], [195, 36, 'Chepalungu'], [196, 36, 'Bomet East'], [197, 36, 'Bomet Central'], [198, 36, 'Konoin'],
            // Kakamega (199-210)
            [199, 37, 'Lugari'], [200, 37, 'Lurambi'], [201, 37, 'Likuyani'], [202, 37, 'Malava'], [203, 37, 'Navakholo'],
            [204, 37, 'Mumias West'], [205, 37, 'Mumias East'], [206, 37, 'Matungu'], [207, 37, 'Butere'], [208, 37, 'Khwisero'], [209, 37, 'Shinyalu'], [210, 37, 'Ikolomani'],
            // Vihiga (211-215)
            [211, 38, 'Vihiga'], [212, 38, 'Sabatia'], [213, 38, 'Hamisi'], [214, 38, 'Luanda'], [215, 38, 'Emuhaya'],
            // Bungoma (216-224)
            [216, 39, 'Mt. Elgon'], [217, 39, 'Sirisia'], [218, 39, 'Kabuchai'], [219, 39, 'Bumula'], [220, 39, 'Kanduyi'],
            [221, 39, 'Webuye East'], [222, 39, 'Webuye West'], [223, 39, 'Kimilili'], [224, 39, 'Tongaren'],
            // Busia (225-231)
            [225, 40, 'Teso North'], [226, 40, 'Teso South'], [227, 40, 'Nambale'], [228, 40, 'Matayos'], [229, 40, 'Butula'], [230, 40, 'Funyula'], [231, 40, 'Budalangi'],
            // Siaya (232-237)
            [232, 41, 'Ugenya'], [233, 41, 'Ugunja'], [234, 41, 'Alego Usonga'], [235, 41, 'Gem'], [236, 41, 'Bondo'], [237, 41, 'Rarieda'],
            // Kisumu (238-244)
            [238, 42, 'Kisumu East'], [239, 42, 'Kisumu West'], [240, 42, 'Kisumu Central'], [241, 42, 'Seme'], [242, 42, 'Nyando'], [243, 42, 'Muhoroni'], [244, 42, 'Nyakach'],
            // Homa Bay (245-252)
            [245, 43, 'Kasipul'], [246, 43, 'Kabondo Kasipul'], [247, 43, 'Karachuonyo'], [248, 43, 'Rangwe'], [249, 43, 'Homa Bay Town'], [250, 43, 'Ndhiwa'], [251, 43, 'Mbita'], [252, 43, 'Suba South'],
            // Migori (253-260)
            [253, 44, 'Rongo'], [254, 44, 'Awendo'], [255, 44, 'Suna East'], [256, 44, 'Suna West'], [257, 44, 'Uriri'], [258, 44, 'Nyatike'], [259, 44, 'Kuria West'], [260, 44, 'Kuria East'],
            // Kisii (261-269)
            [261, 45, 'Bonchari'], [262, 45, 'South Mugirango'], [263, 45, 'Bomachoge Borabu'], [264, 45, 'Bobasi'], [265, 45, 'Bomachoge Chache'],
            [266, 45, 'Nyaribari Masaba'], [267, 45, 'Nyaribari Chache'], [268, 45, 'Kitutu Chache North'], [269, 45, 'Kitutu Chache South'],
            // Nyamira (270-273)
            [270, 46, 'Kitutu Masaba'], [271, 46, 'West Mugirango'], [272, 46, 'North Mugirango'], [273, 46, 'Borabu'],
            // Nairobi (274-290)
            [274, 47, 'Westlands'], [275, 47, 'Dagoretti North'], [276, 47, 'Dagoretti South'], [277, 47, "Lang'ata"], [278, 47, 'Kibra'],
            [279, 47, 'Roysambu'], [280, 47, 'Kasarani'], [281, 47, 'Ruaraka'], [282, 47, 'Embakasi South'], [283, 47, 'Embakasi North'],
            [284, 47, 'Embakasi Central'], [285, 47, 'Embakasi East'], [286, 47, 'Embakasi West'], [287, 47, 'Makadara'], [288, 47, 'Kamukunji'],
            [289, 47, 'Starehe'], [290, 47, 'Mathare']
        ];

        $insertData = [];
        foreach ($constituencies as [$id, $countyId, $name]) {
            $insertData[] = [
                'id' => $id,
                'location_id' => $countyId,
                'description' => $name,
                'status' => 1
            ];
        }

        DB::table('constituencies')->insert($insertData);
    }

    private function seedSampleWards(): void
    {
        // Sample wards for Mombasa, Nairobi, Kiambu only
        $wards = [
            // MOMBASA - 30 wards
            [1, 1, 1, 'PORT REITZ'], [2, 1, 1, 'KIPEVU'], [3, 1, 1, 'AIRPORT'], [4, 1, 1, 'CHANGAMWE'], [5, 1, 1, 'CHAANI'],
            [6, 1, 2, 'JOMVU KUU'], [7, 1, 2, 'MIRITINI'], [8, 1, 2, 'MIKINDANI'],
            [9, 1, 3, 'MJAMBERE'], [10, 1, 3, 'JUNDA'], [11, 1, 3, 'BAMBURI'], [12, 1, 3, 'MWAKIRUNGE'], [13, 1, 3, 'MTOPANGA'], [14, 1, 3, 'MAGOGONI'], [15, 1, 3, 'SHANZU'],
            [16, 1, 4, 'FRERE TOWN'], [17, 1, 4, "ZIWA LA NG'OMBE"], [18, 1, 4, 'MKOMANI'], [19, 1, 4, 'KONGOWEA'], [20, 1, 4, 'KADZANDANI'],
            [21, 1, 5, 'MTONGWE'], [22, 1, 5, 'SHIKA ADABU'], [23, 1, 5, 'BOFU'], [24, 1, 5, 'LIKONI'], [25, 1, 5, 'TIMBWANI'],
            [26, 1, 6, 'MJI WA KALE/MAKADARA'], [27, 1, 6, 'TUDOR'], [28, 1, 6, 'TONONOKA'], [29, 1, 6, 'SHIMANZI/GANJONI'], [30, 1, 6, 'MAJENGO'],
            
            // NAIROBI - Sample 17 wards (Westlands & Mathare)
            [1371, 47, 274, 'KITISURU'], [1372, 47, 274, 'PARKLANDS/HIGHRIDGE'], [1373, 47, 274, 'KARURA'], [1374, 47, 274, 'KANGEMI'], [1375, 47, 274, 'MOUNTAIN VIEW'],
            [1451, 47, 290, 'HURUMA'], [1452, 47, 290, 'NGEI'], [1453, 47, 290, 'MLANGO KUBWA'], [1454, 47, 290, 'KIAMAIKO'], [1455, 47, 290, 'KIAMAIKO'],
            
            // KIAMBU - Sample 12 wards
            [552, 22, 111, 'KIAMWANGI'], [553, 22, 111, 'NDARUGO'], [554, 22, 111, 'NGENDA'], [555, 22, 111, 'GATUNDU SOUTH'],
            [611, 22, 122, 'LARI/KIRENGA'], [612, 22, 122, 'KIRENGA'], [613, 22, 122, 'KIJABE'], [614, 22, 122, 'NYANDUMA']
        ];

        $insertData = [];
        foreach ($wards as [$id, $countyId, $constituencyId, $name]) {
            $insertData[] = [
                'id' => $id,
                'location_id' => $countyId,
                'constituency_id' => $constituencyId,
                'description' => $name,
                'status' => 1
            ];
        }

        foreach (array_chunk($insertData, 50) as $chunk) {
            DB::table('location_area')->insert($chunk);
        }
    }

    private function seedFullWardsFromSql(): void
    {
        $path = database_path($this->sqlDumpPath);

        if (!File::exists($path)) {
            $this->command->warn("SQL file not found: {$path}. Using sample data.");
            $this->seedSampleWards();
            return;
        }

        DB::unprepared(File::get($path));
    }

    private function printSummary(): void
    {
        $counties = DB::table('location')->count();
        $constituencies = DB::table('constituencies')->count();
        $wards = DB::table('location_area')->count();

        $this->command->info(sprintf(
            'Seeded: %d/47 counties, %d/290 constituencies, %d/1450 wards',
            $counties,
            $constituencies,
            $wards
        ));

        if (!$this->importFullDataset) {
            $this->command->info('Tip: Set $importFullDataset = true for all 1,450 wards');
        }
    }
}