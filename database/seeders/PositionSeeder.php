<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Positions')->truncate();
        $items = [
            ["title" => "The Great Adventure", "status" => 1],
            ["title" => "Mystery of the Lost City", "status" => 1],
            ["title" => "Journey to the Unknown", "status" => 1],
            ["title" => "The Secret Garden", "status" => 1],
            ["title" => "Tales of the Forgotten", "status" => 1],
            ["title" => "Legends of the Ancient", "status" => 1],
            ["title" => "The Hidden Treasure", "status" => 1],
            ["title" => "Escape from Alcatraz", "status" => 1],
            ["title" => "The Haunted House", "status" => 1],
            ["title" => "Quest for the Golden Crown", "status" => 1],
            ["title" => "The Enchanted Forest", "status" => 1],
            ["title" => "Battle of the Kings", "status" => 1],
            ["title" => "The Last Warrior", "status" => 1],
            ["title" => "Rise of the Phoenix", "status" => 1],
            ["title" => "The Forbidden Island", "status" => 1],
            ["title" => "Secrets of the Deep", "status" => 1],
            ["title" => "Invasion of the Aliens", "status" => 1],
            ["title" => "The Time Traveler", "status" => 1],
            ["title" => "The Dark Knight", "status" => 1],
            ["title" => "The Magical Realm", "status" => 1],
            ["title" => "The Lost Civilization", "status" => 1],
            ["title" => "The Cursed Amulet", "status" => 1],
            ["title" => "The Dragon's Lair", "status" => 1],
            ["title" => "The Hidden Valley", "status" => 1],
            ["title" => "The Phantom of the Opera", "status" => 1],
            ["title" => "The Royal Heist", "status" => 1],
            ["title" => "The Starship Odyssey", "status" => 1],
            ["title" => "The Final Countdown", "status" => 1],
            ["title" => "The Mystical Journey", "status" => 1],
            ["title" => "The Shadow Warriors", "status" => 1],
            ["title" => "The Enigma Code", "status" => 1],
            ["title" => "The Ghost Ship", "status" => 1],
            ["title" => "The Sunken City", "status" => 1],
            ["title" => "The Hidden Fortress", "status" => 1],
            ["title" => "The Invisible Man", "status" => 1],
            ["title" => "The Pirate's Code", "status" => 1],
            ["title" => "The Arctic Expedition", "status" => 1],
            ["title" => "The Legend of Atlantis", "status" => 1],
            ["title" => "The Secret Agent", "status" => 1],
            ["title" => "The Desert Mirage", "status" => 1],
            ["title" => "The Dark Tower", "status" => 1],
            ["title" => "The Golden Compass", "status" => 1],
            ["title" => "The Lost World", "status" => 1],
            ["title" => "The Frozen Tundra", "status" => 1],
            ["title" => "The Witch's Spell", "status" => 1],
            ["title" => "The Eternal Flame", "status" => 1],
            ["title" => "The Celestial Prophecy", "status" => 1],
            ["title" => "The Robot Uprising", "status" => 1],
            ["title" => "The Secret of Stonehenge", "status" => 1],
            ["title" => "The Vampire Chronicles", "status" => 1],
            ["title" => "The Alien Conspiracy", "status" => 1],
            ["title" => "The Cosmic Voyage", "status" => 1],
            ["title" => "The Mysterious Island", "status" => 1],
            ["title" => "The Dragon's Curse", "status" => 1],
            ["title" => "The Jungle Book", "status" => 1],
            ["title" => "The Treasure Hunt", "status" => 1],
            ["title" => "The Forbidden Planet", "status" => 1],
            ["title" => "The Savage Lands", "status" => 1],
            ["title" => "The Great Escape", "status" => 1],
            ["title" => "The Hidden Temple", "status" => 1],
            ["title" => "The Firebird", "status" => 1],
            ["title" => "The Last Frontier", "status" => 1],
            ["title" => "The Enchanted Castle", "status" => 1],
            ["title" => "The Phantom Thief", "status" => 1],
            ["title" => "The Underwater Kingdom", "status" => 1],
            ["title" => "The Celestial Gate", "status" => 1],
            ["title" => "The Lost Empire", "status" => 1],
            ["title" => "The Titan's Curse", "status" => 1],
            ["title" => "The Astral Plane", "status" => 1],
            ["title" => "The Forgotten Realms", "status" => 1],
            ["title" => "The Crimson Tide", "status" => 1],
            ["title" => "The Dragon's Egg", "status" => 1],
            ["title" => "The Crystal Cave", "status" => 1],
            ["title" => "The Phantom Menace", "status" => 1],
            ["title" => "The Shattered Sword", "status" => 1],
            ["title" => "The Eternal Quest", "status" => 1],
            ["title" => "The Cursed Ship", "status" => 1],
            ["title" => "The Starry Night", "status" => 1],
            ["title" => "The Haunted Castle", "status" => 1],
            ["title" => "The Thunderstorm", "status" => 1],
            ["title" => "The Shadow of the Moon", "status" => 1],
            ["title" => "The Warrior's Path", "status" => 1],
            ["title" => "The Enchanted Forest", "status" => 1],
            ["title" => "The Ghostly Apparition", "status" => 1],
            ["title" => "The Secret of the Sphinx", "status" => 1],
            ["title" => "The Frozen Throne", "status" => 1],
            ["title" => "The Pirate's Treasure", "status" => 1],
            ["title" => "The Serpent's Fang", "status" => 1],
            ["title" => "The Celestial Dragon", "status" => 1],
            ["title" => "The Forgotten Pharaoh", "status" => 1],
            ["title" => "The Endless Horizon", "status" => 1],
            ["title" => "The Enchanted Necklace", "status" => 1],
            ["title" => "The Lost Continent", "status" => 1],
            ["title" => "The Last Stand", "status" => 1],
            ["title" => "The Silver Arrow", "status" => 1],
            ["title" => "The Witch's Broomstick", "status" => 1],
            ["title" => "The Cursed Forest", "status" => 1],
            ["title" => "The Hidden Oasis", "status" => 1],
            ["title" => "The Phantom Ship", "status" => 1],
            ["title" => "The Starry Skies", "status" => 1],
            ["title" => "The Wandering Nomad", "status" => 1],
            ["title" => "The Sunken Treasure", "status" => 1],
            ["title" => "The Enchanted Mirror", "status" => 1]
        ];

        DB::table('Positions')->insert($items);
    }
}
