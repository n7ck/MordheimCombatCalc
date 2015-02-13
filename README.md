# MordheimCombatCalc
Example of work I have done with PHP and AJAX (currently incomplete)
http://cim.saddleback.edu/~ntimmer0/mordCalcExample/mordCalcTest.php

## The PHP 
* reads the contents of every file in the 'mord' directory
* Then generates a html select with javascript event handlers 
* also if there is any parameters in the URL it takes those and updates the selects with the parameter values
* Overall effect is once the submit button is pressed the url is updated so that the page can be linked to with current select values

## The AJAX(AJAJ)
* For testing purposes I still have a submit button but eventually i would get rid of that and just make it all AJAX
* Currently after submit is pressed and the page is refreshed then If there is parameters in the URL a AJAX request is sent to "testReceive.php"
