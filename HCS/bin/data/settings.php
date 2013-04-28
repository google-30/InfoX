<?
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * This file defines default user changable settings
 */

$tuples = array( 
   	       /* Name      Value         Section             Description */	 
	 array( "Logo",     "/example/images/ted_logo.jpg",   "General",     "The logo to display" ),
     array( "HotelName","Synrgic",                    "General",     "The hotel name"),
     array( "Timezone","Asia/Singapore",              "General",     "Timezone"),
     array( "DateFormat","m/d/Y",                     "General",     "Date format"),
     array( "TimeFormat","H:i",                       "General",     "Time format"),
     array( "Language","en_US",                       "General",     "Default language"),
     array( "Currency","S$",                          "General",     "Local currency"),
     array( "HotelAddress","10 Ubi Crescent",         "General",     "The hotel address"),
        
     // A set of settings to the site appearance (style definitions, css attributes)
     array( "background_color", "#fff",                "Styling",     "The bakcground color to the page, ie. #396, lightgray"),
     array( "background_image", "",                    "Styling",     "The bakcground image to the page"),
     array( "background_position", "center",           "Styling",     "The bakcground position to the page"),
     array( "background_repeat", "no-repeat",          "Styling",     "The bakcground repeat to the page"),
     array( "font",      "georgia,sans-serif",         "Styling",     "The font to display" ),
     array( "font_size", "",                           "Styling",     "The font size to display, ie. 20px, 1em" ),
        
     array( "image1",   "/example/images/slideshow/1.png",   "Slideshow", "The logo to display" ),
	 array( "image2",   "/example/images/slideshow/2.png",   "Slideshow", "The logo to display" ),
	 array( "image3",   "/example/images/slideshow/3.png",   "Slideshow", "The logo to display" ),

     // Tablet
     array( "AdvertBoardWidth",   "266",   "Tablet", "The width of the advert board" ),
     array( "AdvertBoardHeight",  "800",   "Tablet", "The height of the advert board" ),
     array( "AdvertWidth",        "266",   "Tablet", "The width of the advert" ),
     array( "AdvertHeight",       "266",   "Tablet", "The height of the advert" ),
     // Table 1920 x 1080
     array( "AdvertBoardWidth",   "287",   "Table", "The width of the advert board" ),
     array( "AdvertBoardHeight",  "1080",   "Table", "The height of the advert board" ),
     array( "AdvertWidth",        "287",   "Table", "The width of the advert" ),
     array( "AdvertHeight",       "270",   "Table", "The height of the advert" ),

     // WelcomeAdverts
     array( "image1",   "/example/images/welcome/1.png",   "WelcomeAdverts", "The WelcomeAdverts to display" ),
	 array( "image2",   "/example/images/welcome/2.png",   "WelcomeAdverts", "The WelcomeAdverts to display" ),
	 array( "image3",   "/example/images/welcome/3.png",   "WelcomeAdverts", "The WelcomeAdverts to display" ),
	 array( "image4",   "/example/images/welcome/4.png",   "WelcomeAdverts", "The WelcomeAdverts to display" ),
	 array( "vertical",   "2",   "WelcomeAdverts", "vertical cells" ),
     array( "horizontal",   "2",   "WelcomeAdverts", "horizontal cells" ),
	 
	 
     // WelcomeImages
     array( "image1",   "/example/images/welcome/1.png",   "WelcomeImages", "Whatisthisfor" ),
        
     // Contact number of hotel
     array( "Operator",   "(65)-888-888-88",   "Contact", "Operator contact number" ),
     array( "Reception",  "(65)-888-888-66",   "Contact", "Reception contact number" ),
    
     // local attractions
     array( "nearbyzoom",       "14",   "LocalAttractions", "zoom number for map view nearby displaying"),
     array( "nearbydistance",   "10000","LocalAttractions", "displaying distance for map view nearby, unit is meter"),    
     array( "centeraddress",    "500 Lorong 6 Toa Payoh Singapore", "LocalAttractions", "City center address for google maps displaying"),
     array( "cityzoom",         "11",   "LocalAttractions", "zoom number for map view nearby displaying"),    

	);

foreach( $tuples as $tuple ){
	$setting = new \Synrgic\Setting();
	$setting->setName($tuple[0]);
	$setting->setValue($tuple[1]);
	$setting->setSection($tuple[2]);
	$setting->setDescription($tuple[3]);
	$em->persist($setting);
}

$em->flush();

?>
