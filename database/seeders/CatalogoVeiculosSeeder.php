<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CatalogoVeiculosSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            'Alfa Romeo'=>['145','147','156','164','2300','Giulia','Spider'],
            'Audi'=>['80','100','A3','A4','A6','Quattro','TT'],
            'BMW'=>['Série 3','Série 5','Série 6','Série 7','2002','Z3'],
            'Chevrolet'=>['Astra','Bel Air','Blazer','Bonanza','Brasil','C-10','C-14','C-20','Camaro','Caravan','Chevette','Corsa','Corvette','D-20','Impala','Kadett','Marajó','Monza','Omega','Opala','Silverado','Veraneio'],
            'Chrysler'=>['300C','Caravan','PT Cruiser','Stratus','Town & Country'],
            'Citroën'=>['2CV','AX','BX','C3','C4','DS','Traction Avant','Xantia','Xsara'],
            'Dodge'=>['Charger','Coronet','Dart','Dakota','Durango','Journey','Le Baron','Magnum','Polara','Ram'],
            'Ferrari'=>['308','328','348','355','360','Dino','F40','Mondial','Testarossa'],
            'Fiat'=>['147','500','Brava','Doblò','Elba','Fiorino','Marea','Oggi','Palio','Panorama','Prêmio','Punto','Spazio','Stilo','Tempra','Tipo','Uno'],
            'Ford'=>['Belina','Bronco','Corcel','Courier','Del Rey','EcoSport','Escort','F-100','F-1000','F-75','Fiesta','Focus','Galaxie','Jeep','Ka','Landau','Maverick','Mondeo','Mustang','Pampa','Ranger','Royale','Verona'],
            'Gurgel'=>['BR-800','Carajás','Ipanema','Itaipu','Supermini','Tocantins','X-12','X-15'],
            'Honda'=>['Accord','Civic','CR-V','Fit','NSX','Prelude'],
            'Hyundai'=>['Accent','Atos','Azera','Elantra','Excel','Galloper','HB20','Santa Fe','Sonata','Tucson'],
            'Jaguar'=>['E-Type','Mark II','S-Type','XJ','XJS','XK'],
            'Jeep'=>['Cherokee','CJ-5','CJ-6','Commander','Compass','Grand Cherokee','Renegade','Willys','Wrangler'],
            'Lada'=>['Laika','Niva','Samara'],
            'Land Rover'=>['Defender','Discovery','Freelander','Range Rover','Série I','Série II','Série III'],
            'Mercedes-Benz'=>['Classe A','Classe C','Classe E','Classe S','190','220','230','250','280','300','350','450','500','SL','SLK'],
            'Mercury'=>['Comet','Cougar','Grand Marquis','Monterey'],
            'Mini'=>['Cooper','Cooper S','Countryman','Moke'],
            'Mitsubishi'=>['3000 GT','Colt','Eclipse','Galant','L200','Lancer','Pajero'],
            'Nissan'=>['300ZX','350Z','Frontier','March','Maxima','Pathfinder','Sentra','Skyline','Versa'],
            'Peugeot'=>['106','205','206','306','405','504','505','605'],
            'Plymouth'=>['Barracuda','Belvedere','Fury','Road Runner','Satellite','Valiant'],
            'Pontiac'=>['Bonneville','Firebird','GTO','LeMans','Trans Am'],
            'Porsche'=>['356','911','914','924','928','944','968','Boxster'],
            'Renault'=>['Clio','Dauphine','Duster','Gordini','Laguna','Mégane','R19','R21','R5','Sandero','Scénic'],
            'Simca'=>['Alvorada','Chambord','Esplanada','Jangada','Presidence','Rallye','Regente','Tufão'],
            'Subaru'=>['Forester','Impreza','Legacy','Outback','SVX'],
            'Suzuki'=>['Grand Vitara','Jimny','Samurai','Swift','Vitara'],
            'Toyota'=>['Bandeirante','Camry','Celica','Corolla','Corona','Hilux','Land Cruiser','MR2','Supra'],
            'Volkswagen'=>['Apollo','Brasília','Corrado','CrossFox','Fox','Fusca','Gol','Golf','Jetta','Karmann Ghia','Kombi','Logus','Parati','Passat','Pointer','Polo','Quantum','Santana','Saveiro','SP1','SP2','TL','Variant','Voyage'],
            'Volvo'=>['240','440','460','740','850','940','C70','S40','S60','S70','V40','V70'],
            'Willys'=>['Aero Willys','Dauphine','Gordini','Interlagos','Itamaraty','Jeep','Rural'],
        ];
        $agora=now();
        foreach($catalogo as $marca=>$modelos){DB::table('marcas_veiculos')->updateOrInsert(['nome'=>$marca],['ativo'=>true,'created_at'=>$agora,'updated_at'=>$agora]);$id=DB::table('marcas_veiculos')->where('nome',$marca)->value('id');foreach($modelos as $modelo){DB::table('modelos_veiculos')->updateOrInsert(['marca_veiculo_id'=>$id,'nome'=>$modelo],['ativo'=>true,'created_at'=>$agora,'updated_at'=>$agora]);}}
    }
}
