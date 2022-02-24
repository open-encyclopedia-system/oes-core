<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('GND_Interface')) {
    /**
     * TODO @nextRelease: read the properties from API?
     */
    class GND_Interface extends API_Interface
    {

        //Set parent parameter
        public string $identifier = 'gnd';
        public string $label = 'GND';
        public bool $css = true;
        public array $copy_options = ['gnd_properties'];
        public array $result_table_header = ['lod_checkbox', 'GND Name', 'Type', 'GND ID', 'lod_link'];

        /* define properties and their arguments https://d-nb.info/standards/elementset/gnd */
        const PROPERTIES = [
            'biographicalOrHistoricalInformation' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#biographicalOrHistoricalInformation', 'label' => ['german' => 'Biografische oder historische Angaben', 'english' => 'Biographical or historical information'], 'position' => 20],
            'gndIdentifier' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#gndIdentifier', 'label' => ['german' => 'GND-Nummer', 'english' => 'GND-Identifier'], 'position' => 10],
            'personalName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#personalName', 'label' => ['german' => 'Persönlicher Name', 'english' => 'Personal name'], 'position' => 210],
            'prefix' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#prefix', 'label' => ['german' => 'Präfix', 'english' => 'Prefix'], 'position' => 110],
            'titleOfNobility' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#titleOfNobility', 'label' => ['german' => 'Adelstitel', 'english' => 'Title of nobility'], 'position' => 120],
            'academicDegree' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#academicDegree', 'label' => ['german' => 'Akademischer Grad', 'english' => 'Academic degree'], 'position' => 100],
            'forename' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#forename', 'label' => ['german' => 'Vorname', 'english' => 'Forename'], 'position' => 140],
            'nameAddition' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#nameAddition', 'label' => ['german' => 'Namensusatz', 'english' => 'Name addition'], 'position' => 130],
            'pseudonym' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#pseudonym', 'label' => ['german' => 'Pseudonym', 'english' => 'Pseudonym'], 'position' => 180],
            'realIdentity' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#realIdentity', 'label' => ['german' => 'Echte Identität', 'english' => 'Real identity'], 'position' => 190],
            'titleOfNobilityAsLiteral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#titleOfNobilityAsLiteral', 'label' => ['german' => 'Adelstitel (Literal)', 'english' => 'Title of nobility (Literal)'], 'position' => 120],
            'surname' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#surname', 'label' => ['german' => 'Nachname', 'english' => 'Surname'], 'position' => 150],
            'preferredName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredName', 'label' => ['german' => 'Bevorzugter Name', 'english' => 'Preferred name'], 'position' => 200],
            'preferredNameEntityForThePerson' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredNameEntityForThePerson', 'label' => ['german' => 'Bevorzugte Namensentität der Person', 'english' => 'Preferred name entity for the person'], 'position' => 300],
            'preferredNameForTheConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredNameForTheConferenceOrEvent', 'label' => ['german' => 'Bevorzugter Name der Konferenz oder Veranstaltung', 'english' => 'Preferred name for the conference or event'], 'position' => 300],
            'preferredNameForTheCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredNameForTheCorporateBody', 'label' => ['german' => 'Bevorzugter Name der Körperschaft', 'english' => 'Preferred name for the corporate body'], 'position' => 300],
            'preferredNameForTheFamily' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredNameForTheFamily', 'label' => ['german' => 'Bevorzugter Name der Familie', 'english' => 'Preferred name for the family'], 'position' => 300],
            'preferredNameForThePerson' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredNameForThePerson', 'label' => ['german' => 'Bevorzugter Name der Person', 'english' => 'Preferred name for the person'], 'position' => 300],
            'preferredNameForThePlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredNameForThePlaceOrGeographicName', 'label' => ['german' => 'Bevorzugter Name des Geografikum', 'english' => 'Preferred name for the place or geographic name'], 'position' => 300],
            'preferredNameForTheSubjectHeading' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredNameForTheSubjectHeading', 'label' => ['german' => 'Bevorzugter Name des Schlagworts', 'english' => 'Preferred name for the subject heading'], 'position' => 300],
            'preferredNameForTheWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#preferredNameForTheWork', 'label' => ['german' => 'Bevorzugter Name des Werks', 'english' => 'Preferred name for the work'], 'position' => 300],
            'temporaryName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#temporaryName', 'label' => ['german' => 'Zeitweiser Name', 'english' => 'Temporary name'], 'position' => 400],
            'temporaryNameOfTheConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#temporaryNameOfTheConferenceOrEvent', 'label' => ['german' => 'Zeitweiser Name der Konferenz oder Veranstaltung', 'english' => 'Temporary name of the conference or event'], 'position' => 400],
            'temporaryNameOfTheCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#temporaryNameOfTheCorporateBody', 'label' => ['german' => 'Zeitweiser Name der Körperschaft', 'english' => 'Temporary name of the corporate body'], 'position' => 400],
            'variantName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantName', 'label' => ['german' => 'Varianter Name', 'english' => 'Variant name'], 'position' => 400],
            'variantNameEntityForThePerson' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantNameEntityForThePerson', 'label' => ['german' => 'Variante Namensentität der Person', 'english' => 'Variant name entity for the person'], 'position' => 400],
            'variantNameForTheConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantNameForTheConferenceOrEvent', 'label' => ['german' => 'Varianter Name der Konferenz oder Veranstaltung', 'english' => 'Variant name for the conference or event'], 'position' => 400],
            'variantNameForTheCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantNameForTheCorporateBody', 'label' => ['german' => 'Varianter Name der Körperschaft', 'english' => 'Variant name for the corporate body'], 'position' => 400],
            'variantNameForTheFamily' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantNameForTheFamily', 'label' => ['german' => 'Varianter Name der Familie', 'english' => 'Variant name for the family'], 'position' => 400],
            'variantNameForThePerson' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantNameForThePerson', 'label' => ['german' => 'Varianter Name der Person', 'english' => 'Variant name for the person'], 'position' => 400],
            'variantNameForThePlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantNameForThePlaceOrGeographicName', 'label' => ['german' => 'Varianter Name des Geografikum', 'english' => 'Variant name for the place or geographic name'], 'position' => 400],
            'variantNameForTheSubjectHeading' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantNameForTheSubjectHeading', 'label' => ['german' => 'Varianter Name des Schlagworts', 'english' => 'Variant name for the subject heading'], 'position' => 400],
            'variantNameForTheWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#variantNameForTheWork', 'label' => ['german' => 'Varianter Name des Werks', 'english' => 'Variant name for the work'], 'position' => 400],
            'abbreviatedNameForTheConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#abbreviatedNameForTheConferenceOrEvent', 'label' => ['german' => 'Abgekürzter Name der Konferenz oder Veranstaltung', 'english' => 'Abbreviated name for the conference or event'], 'position' => 500],
            'abbreviatedNameForTheCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#abbreviatedNameForTheCorporateBody', 'label' => ['german' => 'Abgekürzter Name der Körperschaft', 'english' => 'Abbreviated name for the corporate body'], 'position' => 500],
            'abbreviatedNameForThePlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#abbreviatedNameForThePlaceOrGeographicName', 'label' => ['german' => 'Abgekürzter Name des Geografikum', 'english' => 'Abbreviated name for the place or geographic name'], 'position' => 500],
            'abbreviatedNameForTheWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#abbreviatedNameForTheWork', 'label' => ['german' => 'Abgekürzter Name des Werks', 'english' => 'Abbreviated name for the work'], 'position' => 500],
            'broaderTerm' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#broaderTerm', 'label' => ['german' => 'Oberbegriff', 'english' => 'Broader term'], 'position' => 500],
            'broaderTermGeneral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#broaderTermGeneral', 'label' => ['german' => 'Oberbegriff allgemein', 'english' => 'Broader term (general)'], 'position' => 500],
            'broderTermGeneral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#broderTermGeneral', 'label' => ['german' => 'Oberbegriff allgemein', 'english' => 'Broader term (general)'], 'position' => 500],
            'broaderTermGeneric' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#broaderTermGeneric', 'label' => ['german' => 'Oberbegriff generisch', 'english' => 'Broader term (generic)'], 'position' => 500],
            'broaderTermInstantial' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#broaderTermInstantial', 'label' => ['german' => 'Oberbegriff instantiell', 'english' => 'Broader term (instantial)'], 'position' => 500],
            'broaderTermPartitive' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#broaderTermPartitive', 'label' => ['german' => 'Oberbegriff partitiv', 'english' => 'Broader term (partitive)'], 'position' => 500],
            'broaderTermWithMoreThanOneElement' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#broaderTermWithMoreThanOneElement', 'label' => ['german' => 'Oberbegriff mehrgliedrig', 'english' => 'Broader term (with more than one element)'], 'position' => 500],
            'gndSubjectCategory' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#gndSubjectCategory', 'label' => ['german' => 'GND-Sachgruppe', 'english' => 'GND subject category'], 'position' => 500],
            'narrowerTermGeneral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#narrowerTermGeneral', 'label' => ['german' => 'Unterbegriff allgemein', 'english' => 'Narrower term (general)'], 'position' => 500],
            'narrowerTermGeneric' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#narrowerTermGeneric', 'label' => ['german' => 'Unterbegriff generisch', 'english' => 'Narrower term (generic)'], 'position' => 500],
            'narrowerTermInstantial' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#narrowerTermInstantial', 'label' => ['german' => 'Unterbegriff instantiell', 'english' => 'Narrower term (instantial)'], 'position' => 500],
            'narrowerTermPartitive' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#narrowerTermPartitive', 'label' => ['german' => 'Unterbegriff partitiv', 'english' => 'Narrower term (partitive)'], 'position' => 500],
            'dateOfBirth' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfBirth', 'label' => ['german' => 'Geburtsdatum', 'english' => 'Date of birth'], 'position' => 1010],
            'dateOfBirthAndDeath' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfBirthAndDeath', 'label' => ['german' => 'Geburts- und Sterbedatum', 'english' => 'Date of birth and death'], 'position' => 1000],
            'dateOfDeath' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfDeath', 'label' => ['german' => 'Sterbedatum', 'english' => 'Date of death'], 'position' => 1040],
            'gender' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#gender', 'label' => ['german' => 'Geschlecht', 'english' => 'Gender'], 'position' => 1100],
            'placeOfBirth' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfBirth', 'label' => ['german' => 'Geburtsort', 'english' => 'Place of Birth'], 'position' => 1020],
            'placeOfBirthAsLiteral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfBirthAsLiteral', 'label' => ['german' => 'Geburtsort (Literal)', 'english' => 'Place of Birth (Literal)'], 'position' => 1030],
            'placeOfDeath' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfDeath', 'label' => ['german' => 'Sterbeort', 'english' => 'Place of death'], 'position' => 1060],
            'placeOfDeathAsLiteral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfDeathAsLiteral', 'label' => ['german' => 'Sterbeort (Literal)', 'english' => 'Place of death (Literal)'], 'position' => 1070],
            'acquaintanceshipOrFriendship' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#acquaintanceshipOrFriendship', 'label' => ['german' => 'Beziehung, Bekanntschaft, Freundschaft', 'english' => 'Acquaintanceship or friendship'], 'position' => 1200],
            'familialRelationship' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#familialRelationship', 'label' => ['german' => 'Familiäre Beziehung', 'english' => 'Familial relationship'], 'position' => 1200],
            'member' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#member', 'label' => ['german' => 'Mitglied', 'english' => 'Member'], 'position' => 1200],
            'memberOfTheFamily' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#memberOfTheFamily', 'label' => ['german' => 'Familienmitglied', 'english' => 'Member of the family'], 'position' => 1200],
            'place' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#place', 'label' => ['german' => 'Ort', 'english' => 'Place'], 'position' => 1300],
            'placeOfActivity' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfActivity', 'label' => ['german' => 'Wirkungsort', 'english' => 'Place of activity'], 'position' => 1300],
            'placeOfBusiness' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfBusiness', 'label' => ['german' => 'Sitz', 'english' => 'Place of business'], 'position' => 1300],
            'placeOfCustody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfCustody', 'label' => ['german' => 'Aufbewahrungsort', 'english' => 'Place of custody'], 'position' => 1300],
            'placeOfExile' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfExile', 'label' => ['german' => 'Exilort', 'english' => 'Place of Exile'], 'position' => 1300],
            'affiliation' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#affiliation', 'label' => ['german' => 'Affiliation', 'english' => 'Affiliation'], 'position' => 1400],
            'affiliationAsLiteral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#affiliationAsLiteral', 'label' => ['german' => 'Affiliation (Literal)', 'english' => 'Affiliation (Literal)'], 'position' => 1400],
            'fieldOfActivity' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#fieldOfActivity', 'label' => ['german' => 'Tätigkeitsbereich', 'english' => 'Field of activity'], 'position' => 1400],
            'fieldOfStudy' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#fieldOfStudy', 'label' => ['german' => 'Studienfach', 'english' => 'Field of study'], 'position' => 1400],
            'professionOrOccupation' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#professionOrOccupation', 'label' => ['german' => 'Beruf oder Beschäftigung', 'english' => 'Profession or occupation'], 'position' => 1400],
            'professionOrOccupationAsLiteral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#professionOrOccupationAsLiteral', 'label' => ['german' => 'Beruf oder Beschäftigung (Literal)', 'english' => 'Profession or occupation (Literal)'], 'position' => 1400],
            'professionalRelationship' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#professionalRelationship', 'label' => ['german' => 'Berufliche Beziehung', 'english' => 'Professional relationship'], 'position' => 1400],
            'otherPlace' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#otherPlace', 'label' => ['german' => 'Weiterer Ort', 'english' => 'Other place'], 'position' => 2000],
            'spatialAreaOfActivity' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#spatialAreaOfActivity', 'label' => ['german' => 'Geographischer Wirkungsbereich', 'english' => 'Spatial area of activity'], 'position' => 2000],
            'geographicAreaCode' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#geographicAreaCode', 'label' => ['german' => 'Ländercode', 'english' => 'Geographic Area Code'], 'position' => 4000],
            'coordinates' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#coordinates', 'label' => ['german' => 'Koordinaten', 'english' => 'Coordinates'], 'position' => 4000],
            'typeOfCoordinates' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#typeOfCoordinates', 'label' => ['german' => 'Koordinatentyp', 'english' => 'Type of coordinates'], 'position' => 4000],
            'easternmostLongitude' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#easternmostLongitude', 'label' => ['german' => 'Östlichster Längengrad', 'english' => 'Easternmost longitude'], 'position' => 4200],
            'northernmostLatitude' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#northernmostLatitude', 'label' => ['german' => 'Nördlichster Breitengrad', 'english' => 'Northernmost latitude'], 'position' => 4100],
            'southernmostLatitude' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#southernmostLatitude', 'label' => ['german' => 'Südlichster Breitengrad', 'english' => 'Southernmost latitude'], 'position' => 4300],
            'westernmostLongitude' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#westernmostLongitude', 'label' => ['german' => 'Westlichster Längengrad', 'english' => 'Westernmost longitude'], 'position' => 4400],
            'startingOrFinalPointOfADistance' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#startingOrFinalPointOfADistance', 'label' => ['german' => 'Beginn und Ende einer Strecke', 'english' => 'Starting or final point of a distance'], 'position' => 4500],
            'udkCode' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#udkCode', 'label' => ['german' => 'UDK-Code', 'english' => 'UDK-Code'], 'position' => 4500],
            'accordingWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#accordingWork', 'label' => ['german' => 'Zugehöriges Werk', 'english' => 'According work'], 'position' => 8000],
            'accreditedArtist' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#accreditedArtist', 'label' => ['german' => 'Zugeschriebener Künstler', 'english' => 'Accredited artist'], 'position' => 8000],
            'accreditedAuthor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#accreditedAuthor', 'label' => ['german' => 'Zugeschriebener Verfasser', 'english' => 'Accredited author'], 'position' => 8000],
            'accreditedComposer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#accreditedComposer', 'label' => ['german' => 'Zugeschriebener Komponist', 'english' => 'Accredited composer'], 'position' => 8000],
            'placeOfDiscovery' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfDiscovery', 'label' => ['german' => 'Fundort', 'english' => 'Place of discovery'], 'position' => 8000],
            'placeOfManufacture' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfManufacture', 'label' => ['german' => 'Herstellungsort', 'english' => 'Place of manufacture'], 'position' => 8000],
            'annotator' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#annotator', 'label' => ['german' => 'Annotator', 'english' => 'Annotator'], 'position' => 8000],
            'architect' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#architect', 'label' => ['german' => 'Architekt', 'english' => 'Architect'], 'position' => 8000],
            'arranger' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#arranger', 'label' => ['german' => 'Arrangeur', 'english' => 'Arranger'], 'position' => 8000],
            'artist' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#artist', 'label' => ['german' => 'Künstler', 'english' => 'Artist'], 'position' => 8000],
            'associatedDate' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#associatedDate', 'label' => ['german' => 'Assoziierte Zeit', 'english' => 'Associated date'], 'position' => 8000],
            'associatedPlace' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#associatedPlace', 'label' => ['german' => 'Assoziierter Ort', 'english' => 'Associated place'], 'position' => 8000],
            'author' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#author', 'label' => ['german' => 'Verfasser', 'english' => 'Author'], 'position' => 8000],
            'beginningOfPeriod' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#beginningOfPeriod', 'label' => ['german' => 'Begin einer Periode', 'english' => 'Beginning of a period'], 'position' => 8000],
            'benefactor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#benefactor', 'label' => ['german' => 'Stifter', 'english' => 'Benefactor'], 'position' => 8000],
            'bookbinder' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#bookbinder', 'label' => ['german' => 'Buchbinder', 'english' => 'Bookbinder'], 'position' => 8000],
            'bookdesigner' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#bookdesigner', 'label' => ['german' => 'Buchgestalter', 'english' => 'Bookdesigner'], 'position' => 8000],
            'buildingOwner' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#buildingOwner', 'label' => ['german' => 'Bauherr', 'english' => 'Building owner'], 'position' => 8000],
            'cartographer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#cartographer', 'label' => ['german' => 'Kartograf', 'english' => 'Cartographer'], 'position' => 8000],
            'celebratedCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#celebratedCorporateBody', 'label' => ['german' => 'Gefeierte Körperschaft', 'english' => 'Celebrated corporate body'], 'position' => 8000],
            'celebratedFamily' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#celebratedFamily', 'label' => ['german' => 'Gefeierte Familie', 'english' => 'Celebrated family'], 'position' => 8000],
            'celebratedPerson' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#celebratedPerson', 'label' => ['german' => 'Gefeierte Person', 'english' => 'Celebrated person'], 'position' => 8000],
            'celebratedTopic' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#celebratedTopic', 'label' => ['german' => 'Gefeiertes Thema', 'english' => 'Celebrated topic'], 'position' => 8000],
            'characteristicPlace' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#characteristicPlace', 'label' => ['german' => 'Charakteristischer Ort', 'english' => 'Characteristic place'], 'position' => 8000],
            'choreographer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#choreographer', 'label' => ['german' => 'Choreograf', 'english' => 'Choreographer'], 'position' => 8000],
            'citedArtist' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#citedArtist', 'label' => ['german' => 'Zitierter Künstler', 'english' => 'Cited artist'], 'position' => 8000],
            'citedAuthor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#citedAuthor', 'label' => ['german' => 'Zitierter Verfasser', 'english' => 'Cited author'], 'position' => 8000],
            'citedComposer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#citedComposer', 'label' => ['german' => 'Zitierter Komponist', 'english' => 'Cited composer'], 'position' => 8000],
            'collector' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#collector', 'label' => ['german' => 'Sammler', 'english' => 'Collector'], 'position' => 8000],
            'compiler' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#compiler', 'label' => ['german' => 'Kompilator', 'english' => 'Compiler'], 'position' => 8000],
            'complexSeeReferenceSubject' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#complexSeeReferenceSubject', 'label' => ['german' => 'Relationierter Deskriptor', 'english' => 'Complex see reference - subject'], 'position' => 8000],
            'composer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#composer', 'label' => ['german' => 'Komponist', 'english' => 'Composer'], 'position' => 8000],
            'conferrer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#conferrer', 'label' => ['german' => 'Leihgeber', 'english' => 'Conferrer'], 'position' => 8000],
            'contributingCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#contributingCorporateBody', 'label' => ['german' => 'Beteiligte Körperschaft', 'english' => 'Contributing corporate body'], 'position' => 8000],
            'contributingFamily' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#contributingFamily', 'label' => ['german' => 'Beteiligte Familie', 'english' => 'Contributing family'], 'position' => 8000],
            'contributinFamily' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#contributinFamily', 'label' => ['german' => 'Beteiligte Familie', 'english' => 'Contributing family'], 'position' => 8000],
            'contributingPerson' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#contributingPerson', 'label' => ['german' => 'Beteiligte Person', 'english' => 'Contributing person'], 'position' => 8000],
            'contributingPlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#contributingPlaceOrGeographicName', 'label' => ['german' => 'Beteiligtes Geografikum', 'english' => 'Contributing place or geographic name'], 'position' => 8000],
            'copist' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#copist', 'label' => ['german' => 'Kopist', 'english' => 'Copist'], 'position' => 8000],
            'corporateBodyIsMember' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#corporateBodyIsMember', 'label' => ['german' => 'Körperschaft ist Mitglied', 'english' => 'Corporate body is member'], 'position' => 8000],
            'correspondent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#correspondent', 'label' => ['german' => 'Korrespondenzpartner', 'english' => 'Correspondent'], 'position' => 8000],
            'counting' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#counting', 'label' => ['german' => 'Zählung', 'english' => 'Counting'], 'position' => 8000],
            'creator' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#creator', 'label' => ['german' => 'Urheber', 'english' => 'Creator'], 'position' => 8000],
            'curator' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#curator', 'label' => ['german' => 'Kurator', 'english' => 'Curator'], 'position' => 8000],
            'dateOfConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfConferenceOrEvent', 'label' => ['german' => 'Veranstaltungsdaten', 'english' => 'Date of conference or event'], 'position' => 8000],
            'dateOfDiscovery' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfDiscovery', 'label' => ['german' => 'Fundjahr', 'english' => 'Date of discovery'], 'position' => 8000],
            'dateOfEstablishment' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfEstablishment', 'label' => ['german' => 'Gründungsdatum', 'english' => 'Date of establishment'], 'position' => 8000],
            'dateOfEstablishmentAndTermination' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfEstablishmentAndTermination', 'label' => ['german' => 'Gründungs- und Auflösungsdatum', 'english' => 'Date of establishment and termination'], 'position' => 8000],
            'dateOfProduction' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfProduction', 'label' => ['german' => 'Erstellungszeit', 'english' => 'Date of production'], 'position' => 8000],
            'dateOfPublication' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfPublication', 'label' => ['german' => 'Erscheinungszeit', 'english' => 'Date of publication'], 'position' => 8000],
            'dateOfTermination' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dateOfTermination', 'label' => ['german' => 'Auflösungsdatum', 'english' => 'Date of termination'], 'position' => 8000],
            'dedicatee' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#dedicatee', 'label' => ['german' => 'Widmungsempfänger', 'english' => 'Dedicatee'], 'position' => 8000],
            'definition' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#definition', 'label' => ['german' => 'Definition', 'english' => 'Definition'], 'position' => 8000],
            'designer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#designer', 'label' => ['german' => 'Designer', 'english' => 'Designer'], 'position' => 8000],
            'director' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#director', 'label' => ['german' => 'Regisseur', 'english' => 'Director'], 'position' => 8000],
            'directorOfPhotography' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#directorOfPhotography', 'label' => ['german' => 'Verantwortlicher Kameramann', 'english' => 'Director of photography'], 'position' => 8000],
            'doubtfulArtist' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#doubtfulArtist', 'label' => ['german' => 'Angezweifelter Künstler', 'english' => 'Doubtful artist'], 'position' => 8000],
            'doubtfulAuthor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#doubtfulAuthor', 'label' => ['german' => 'Angezweifelter Verfasser', 'english' => 'Doubtful author'], 'position' => 8000],
            'doubtfulComposer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#doubtfulComposer', 'label' => ['german' => 'Angezweifelter Komponist', 'english' => 'Doubtful composer'], 'position' => 8000],
            'editor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#editor', 'label' => ['german' => 'Herausgeber', 'english' => 'Editor'], 'position' => 8000],
            'endOfPeriod' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#endOfPeriod', 'label' => ['german' => 'Ende einer Periode', 'english' => 'End of a period'], 'position' => 8000],
            'engraver' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#engraver', 'label' => ['german' => 'Graveur', 'english' => 'Engraver'], 'position' => 8000],
            'epithetGenericNameTitleOrTerritory' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#epithetGenericNameTitleOrTerritory', 'label' => ['german' => 'Beiname, Gattungsname, Titulatur, Territorium', 'english' => 'Epithet, generic name, title or territory'], 'position' => 8000],
            'etcher' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#etcher', 'label' => ['german' => 'Radierer', 'english' => 'Etcher'], 'position' => 8000],
            'exhibitor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#exhibitor', 'label' => ['german' => 'Aussteller', 'english' => 'Exhibitor'], 'position' => 8000],
            'fictitiousAuthor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#fictitiousAuthor', 'label' => ['german' => 'Fiktiver Verfasser', 'english' => 'Fictitious author'], 'position' => 8000],
            'firstArtist' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#firstArtist', 'label' => ['german' => 'Erster Künstler', 'english' => 'First artist'], 'position' => 8000],
            'firstAuthor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#firstAuthor', 'label' => ['german' => 'Erste Verfasserschaft', 'english' => 'First author'], 'position' => 8000],
            'firstComposer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#firstComposer', 'label' => ['german' => 'Erster Komponist', 'english' => 'First composer'], 'position' => 8000],
            'formOfWorkAndExpression' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#formOfWorkAndExpression', 'label' => ['german' => 'Form des Werks und der Expression', 'english' => 'Form of work and expression'], 'position' => 8000],
            'formerOwner' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#formerOwner', 'label' => ['german' => 'Früherer Besitzer', 'english' => 'Former owner'], 'position' => 8000],
            'founder' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#founder', 'label' => ['german' => 'Gründer', 'english' => 'Founder'], 'position' => 8000],
            'functionOrRole' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#functionOrRole', 'label' => ['german' => 'Funktion oder Rolle', 'english' => 'Function or role'], 'position' => 8000],
            'functionOrRoleAsLiteral' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#functionOrRoleAsLiteral', 'label' => ['german' => 'Funktion oder Rolle (Literal)', 'english' => 'Function or role (Literal)'], 'position' => 8000],
            'hierarchicalSuperior' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#hierarchicalSuperior', 'label' => ['german' => 'Administrative Überordnung', 'english' => 'Hierarchical superior'], 'position' => 8000],
            'hierarchicalSuperiorOfPlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#hierarchicalSuperiorOfPlaceOrGeographicName', 'label' => ['german' => 'Administrative Überordnung des Geografikums', 'english' => 'Hierarchical superior of place or geographic name'], 'position' => 8000],
            'hierarchicalSuperiorOfTheConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#hierarchicalSuperiorOfTheConferenceOrEvent', 'label' => ['german' => 'Administrative Überordnung der Konferenz oder der Veranstaltung', 'english' => 'Hierarchical superior of the conference or event'], 'position' => 8000],
            'hierarchicalSuperiorOfTheCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#hierarchicalSuperiorOfTheCorporateBody', 'label' => ['german' => 'Administrative Überordnung der Körperschaft', 'english' => 'Hierarchical superior of the corporate body'], 'position' => 8000],
            'homepage' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#homepage', 'label' => ['german' => 'Homepage', 'english' => 'Homepage'], 'position' => 8000],
            'illustratorOrIlluminator' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#illustratorOrIlluminator', 'label' => ['german' => 'Illustrator oder Illuminator', 'english' => 'Illustrator or illuminator'], 'position' => 8000],
            'initiator' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#initiator', 'label' => ['german' => 'Veranlasser', 'english' => 'Initiator'], 'position' => 8000],
            'instrument' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#instrument', 'label' => ['german' => 'Instrument', 'english' => 'Instrument'], 'position' => 8000],
            'instrumentalist' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#instrumentalist', 'label' => ['german' => 'Instrumentalmusiker', 'english' => 'Instrumentalist'], 'position' => 8000],
            'inventor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#inventor', 'label' => ['german' => 'Erfinder', 'english' => 'Inventor'], 'position' => 8000],
            'keyOfTheVersion' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#keyOfTheVersion', 'label' => ['german' => 'Tonart der Fassung', 'english' => 'Key of the version'], 'position' => 8000],
            'keyOfTheWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#keyOfTheWork', 'label' => ['german' => 'Tonart des Werks', 'english' => 'Key of the work'], 'position' => 8000],
            'language' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#language', 'label' => ['german' => 'Sprache', 'english' => 'Language'], 'position' => 8000],
            'languageCode' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#languageCode', 'label' => ['german' => 'Sprachencode', 'english' => 'Language code'], 'position' => 8000],
            'librettist' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#librettist', 'label' => ['german' => 'Librettist', 'english' => 'Librettist'], 'position' => 8000],
            'literarySource' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#literarySource', 'label' => ['german' => 'Vorlage', 'english' => 'Literary source'], 'position' => 8000],
            'lithographer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#lithographer', 'label' => ['german' => 'Litograf', 'english' => 'Lithographer'], 'position' => 8000],
            'manufacturer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#manufacturer', 'label' => ['german' => 'Hersteller', 'english' => 'Manufacturer'], 'position' => 8000],
            'marc21equivalent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#marc21equivalent', 'label' => ['german' => 'MARC 21 Entsprechung', 'english' => 'MARC 21 equivalent'], 'position' => 8000],
            'mediumOfPerformance' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#mediumOfPerformance', 'label' => ['german' => 'Besetzung im Musikbereich', 'english' => 'Medium of performance'], 'position' => 8000],
            'musician' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#musician', 'label' => ['german' => 'Musiker', 'english' => 'Musician'], 'position' => 8000],
            'narrator' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#narrator', 'label' => ['german' => 'Sprecher', 'english' => 'Narrator'], 'position' => 8000],
            'occasion' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#occasion', 'label' => ['german' => 'Anlass', 'english' => 'Occasion'], 'position' => 8000],
            'occasionOfTheSubjectHeading' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#occasionOfTheSubjectHeading', 'label' => ['german' => 'Anlass des Schlagworts', 'english' => 'Occasion of the subject heading'], 'position' => 8000],
            'occasionOfTheWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#occasionOfTheWork', 'label' => ['german' => 'Anlass des Werkes', 'english' => 'Occasion of the work'], 'position' => 8000],
            'opusNumericDesignationOfMusicalWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#opusNumericDesignationOfMusicalWork', 'label' => ['german' => 'Opus-Zählung des Musikwerks', 'english' => 'Opus numeric designation of musical work'], 'position' => 8000],
            'organizerOrHost' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#organizerOrHost', 'label' => ['german' => 'Veranstalter oder Gastgeber', 'english' => 'Organizer or host'], 'position' => 8000],
            'owner' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#owner', 'label' => ['german' => 'Besitzer', 'english' => 'Owner'], 'position' => 8000],
            'painter' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#painter', 'label' => ['german' => 'Maler', 'english' => 'Painter'], 'position' => 8000],
            'periodOfActivity' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#periodOfActivity', 'label' => ['german' => 'Wirkungsdaten', 'english' => 'Period of activity'], 'position' => 8000],
            'photographer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#photographer', 'label' => ['german' => 'Fotograf', 'english' => 'Photographer'], 'position' => 8000],
            'placeOfConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOfConferenceOrEvent', 'label' => ['german' => 'Veranstaltungsort', 'english' => 'Place of conference or event'], 'position' => 8000],
            'placeOrGeographicNameIsMember' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#placeOrGeographicNameIsMember', 'label' => ['german' => 'Geografikum ist Mitglied', 'english' => 'Place or geographic name is member'], 'position' => 8000],
            'playedInstrument' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#playedInstrument', 'label' => ['german' => 'Gespieltes Instrument', 'english' => 'Played instrument'], 'position' => 8000],
            'poet' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#poet', 'label' => ['german' => 'Dichter', 'english' => 'Poet'], 'position' => 8000],
            'precedingConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#precedingConferenceOrEvent', 'label' => ['german' => 'Vorherige Konferenz oder Veranstaltung', 'english' => 'Preceding conference or event'], 'position' => 8000],
            'precedingCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#precedingCorporateBody', 'label' => ['german' => 'Vorherige Körperschaft', 'english' => 'Preceding corporate body'], 'position' => 8000],
            'precedingPlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#precedingPlaceOrGeographicName', 'label' => ['german' => 'Vorheriges Geografikum', 'english' => 'Preceding place or geographic name'], 'position' => 8000],
            'precedingSubject' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#precedingSubject', 'label' => ['german' => 'Vorheriges Schlagwort', 'english' => 'Preceding subject heading'], 'position' => 8000],
            'precedingWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#precedingWork', 'label' => ['german' => 'Vorheriges Werk', 'english' => 'Preceding work'], 'position' => 8000],
            'predecessor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#predecessor', 'label' => ['german' => 'Vorgänger', 'english' => 'Predecessor'], 'position' => 8000],
            'printer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#printer', 'label' => ['german' => 'Drucker', 'english' => 'Printer'], 'position' => 8000],
            'publication' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#publication', 'label' => ['german' => 'Titelangabe', 'english' => 'Publication'], 'position' => 8000],
            'restorer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#restorer', 'label' => ['german' => 'Restaurator', 'english' => 'Restorer'], 'position' => 8000],
            'revisor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#revisor', 'label' => ['german' => 'Bearbeiter', 'english' => 'Revisor'], 'position' => 8000],
            'screenwriter' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#screenwriter', 'label' => ['german' => 'Drehbuchautor', 'english' => 'Screenwriter'], 'position' => 8000],
            'scriptorium' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#scriptorium', 'label' => ['german' => 'Skriptorium', 'english' => 'Scriptorium'], 'position' => 8000],
            'sculptor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#sculptor', 'label' => ['german' => 'Bildhauer', 'english' => 'Sculptor'], 'position' => 8000],
            'serialNumericDesignationOfMusicalWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#serialNumericDesignationOfMusicalWork', 'label' => ['german' => 'Fortlaufende Zählung des Musikwerks', 'english' => 'Serial numeric designation of musical work'], 'position' => 8000],
            'singer' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#singer', 'label' => ['german' => 'Sänger', 'english' => 'Singer'], 'position' => 8000],
            'sponsorOrPatron' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#sponsorOrPatron', 'label' => ['german' => 'Sponsor oder Mäzen', 'english' => 'Sponsor or patron'], 'position' => 8000],
            'subeditor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#subeditor', 'label' => ['german' => 'Redakteur', 'english' => 'Subeditor'], 'position' => 8000],
            'succeedingConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#succeedingConferenceOrEvent', 'label' => ['german' => 'Nachfolgende Konferenz oder Veranstaltung', 'english' => 'Succeeding conference or event'], 'position' => 8000],
            'succeedingCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#succeedingCorporateBody', 'label' => ['german' => 'Nachfolgende Körperschaft', 'english' => 'Succeeding corporate body'], 'position' => 8000],
            'succeedingPlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#succeedingPlaceOrGeographicName', 'label' => ['german' => 'Nachfolgendes Geografikum', 'english' => 'Succeeding place or geographic name'], 'position' => 8000],
            'succeedingSubjectHeading' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#succeedingSubjectHeading', 'label' => ['german' => 'Nachfolgendes Schlagwort', 'english' => 'Succeeding subject heading'], 'position' => 8000],
            'succeedingWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#succeedingWork', 'label' => ['german' => 'Nachfolgendes Werk', 'english' => 'Succeeding work'], 'position' => 8000],
            'successor' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#successor', 'label' => ['german' => 'Nachfolger', 'english' => 'Successor'], 'position' => 8000],
            'superPropertyOf' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#superPropertyOf', 'label' => ['german' => 'Super-Property von', 'english' => 'Super-property of'], 'position' => 8000],
            'topic' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#topic', 'label' => ['german' => 'Thema', 'english' => 'Topic'], 'position' => 8000],
            'translator' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#translator', 'label' => ['german' => 'Übersetzer', 'english' => 'Translator'], 'position' => 8000],
            'temporaryNameOfThePlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#temporaryNameOfThePlaceOrGeographicName', 'label' => ['german' => 'Zeitweiser Name des Geografikums', 'english' => 'Temporary name of the place or geographic name'], 'position' => 8000],
            'thematicIndexNumericDesignationOfMusicalWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#thematicIndexNumericDesignationOfMusicalWork', 'label' => ['german' => 'Zählung eines Werksverzeichnisses des Musikwerks', 'english' => 'Thematic index numeric designation of musical work'], 'position' => 8000],
            'addressee' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#addressee', 'label' => ['german' => 'Adressat', 'english' => 'Addressee'], 'position' => 8000],
            'addition' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#addition', 'label' => ['german' => 'Zusatz', 'english' => 'Addition'], 'position' => 9600],
            'relatedConferenceOrEvent' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedConferenceOrEvent', 'label' => ['german' => 'In Beziehung stehende Konferenz oder Veranstaltung', 'english' => 'Related conference or event'], 'position' => 9700],
            'relatedCorporateBody' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedCorporateBody', 'label' => ['german' => 'In Beziehung stehende Körperschaft', 'english' => 'Related Corporate Body'], 'position' => 9700],
            'relatedDdcWithDegreeOfDeterminacy1' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedDdcWithDegreeOfDeterminacy1', 'label' => ['german' => 'In Beziehung stehende Dewey-Dezimalklassifikation mit Determiniertheitsgrad 1', 'english' => 'Related Dewey Decimal Classification with degree of determinacy 1'], 'position' => 9700],
            'relatedDdcWithDegreeOfDeterminacy2' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedDdcWithDegreeOfDeterminacy2', 'label' => ['german' => 'In Beziehung stehende Dewey-Dezimalklassifikation mit Determiniertheitsgrad 2', 'english' => 'Related Dewey Decimal Classification with degree of determinacy 2'], 'position' => 9700],
            'relatedDdcWithDegreeOfDeterminacy3' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedDdcWithDegreeOfDeterminacy3', 'label' => ['german' => 'In Beziehung stehende Dewey-Dezimalklassifikation mit Determiniertheitsgrad 3', 'english' => 'Related Dewey Decimal Classification with degree of determinacy 3'], 'position' => 9700],
            'relatedDdcWithDegreeOfDeterminacy4' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedDdcWithDegreeOfDeterminacy4', 'label' => ['german' => 'In Beziehung stehende Dewey-Dezimalklassifikation mit Determiniertheitsgrad 4', 'english' => 'Related Dewey Decimal Classification with degree of determinacy 4'], 'position' => 9700],
            'relatedFamily' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedFamily', 'label' => ['german' => 'In Beziehung stehende Familie', 'english' => 'Related family'], 'position' => 9700],
            'relatedPerson' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedPerson', 'label' => ['german' => 'In Beziehung stehende Person', 'english' => 'Related person'], 'position' => 9700],
            'relatedPlaceOrGeographicName' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedPlaceOrGeographicName', 'label' => ['german' => 'In Beziehung stehendes Geografikum', 'english' => 'Related place or geographic name'], 'position' => 9700],
            'relatedSubjectHeading' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedSubjectHeading', 'label' => ['german' => 'In Beziehung stehendes Schlagwort', 'english' => 'Related subject heading'], 'position' => 9700],
            'relatedSubjecHeading' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedSubjecHeading', 'label' => ['german' => 'In Beziehung stehendes Schlagwort', 'english' => 'Related subject heading'], 'position' => 9700],
            'relatedTerm' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedTerm', 'label' => ['german' => 'Verwandter Begriff', 'english' => 'Related Term'], 'position' => 9700],
            'relatedWork' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#relatedWork', 'label' => ['german' => 'In Beziehung stehendes Werk', 'english' => 'Related work'], 'position' => 9700],
            'oldAuthorityNumber' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#oldAuthorityNumber', 'label' => ['german' => 'Alte Normnummer', 'english' => 'Old authority number'], 'position' => 9800],
            'writerOfAddedCommentary' =>  ['link' => 'https://d-nb.info/standards/elementset/gnd#writerOfAddedCommentary', 'label' => ['german' => 'Kommentator (schriftlich)', 'english' => 'Writer of added commentary'], 'position' => 9900],
        ];

        /* define types and their arguments */
        const TYPES = [
            'AuthorityResource' => ['labels' => ['english' => 'Authority Resource', 'german' => 'Normdatenressource'], 'parents' => [], 'identifier' => 'AuthorityResource'],
            'NameOfThePerson' => ['labels' => ['english' => 'Name of the person', 'german' => 'Personenname'], 'parents' => [], 'identifier' => 'NameOfThePerson'],
            'ConferenceOrEvent' => ['labels' => ['english' => 'Conference or Event', 'german' => 'Konferenz oder Veranstaltung'], 'parents' => ['AuthorityResource'], 'identifier' => 'ConferenceOrEvent'],
            'CorporateBody' => ['labels' => ['english' => 'Corporate Body', 'german' => 'Körperschaft'], 'parents' => ['AuthorityResource'], 'identifier' => 'CorporateBody'],
            'Family' => ['labels' => ['english' => 'Family', 'german' => 'Familie'], 'parents' => ['AuthorityResource'], 'identifier' => 'Family'],
            'Person' => ['labels' => ['english' => 'Person', 'german' => 'Person'], 'parents' => ['AuthorityResource'], 'identifier' => 'Person'],
            'PlaceOrGeographicName' => ['labels' => ['english' => 'Place or geographic name', 'german' => 'Geografikum'], 'parents' => ['AuthorityResource'], 'identifier' => 'PlaceOrGeographicName'],
            'SubjectHeading' => ['labels' => ['english' => 'Subject heading', 'german' => 'Schlagwort'], 'parents' => ['AuthorityResource'], 'identifier' => 'SubjectHeading'],
            'Work' => ['labels' => ['english' => 'Work', 'german' => 'Werk'], 'parents' => ['AuthorityResource'], 'identifier' => 'Work'],
            'SeriesOfConferenceOrEvent' => ['labels' => ['english' => 'Series of conference or event', 'german' => 'Kongressfolge oder Veranstaltungsfolge'], 'parents' => ['AuthorityResource', 'ConferenceOrEvent'], 'identifier' => 'ConferenceOrEvent'],
            'Company' => ['labels' => ['english' => 'Company', 'german' => 'Firma'], 'parents' => ['AuthorityResource', 'CorporateBody'], 'identifier' => 'CorporateBody'],
            'FictiveCorporateBody' => ['labels' => ['english' => 'Fictive corporate body', 'german' => 'Fiktive Körperschaft'], 'parents' => ['AuthorityResource', 'CorporateBody'], 'identifier' => 'CorporateBody'],
            'MusicalCorporateBody' => ['labels' => ['english' => 'Musical corporate body', 'german' => 'Musikalische Körperschaft'], 'parents' => ['AuthorityResource', 'CorporateBody'], 'identifier' => 'CorporateBody'],
            'OrganOfCorporateBody' => ['labels' => ['english' => 'Organ of corporate body', 'german' => 'Organ einer Körperschaft'], 'parents' => ['AuthorityResource', 'CorporateBody'], 'identifier' => 'CorporateBody'],
            'ProjectOrProgram' => ['labels' => ['english' => 'Project or program', 'german' => 'Projekt oder Programm'], 'parents' => ['AuthorityResource', 'CorporateBody'], 'identifier' => 'CorporateBody'],
            'ReligiousAdministrativeUnit' => ['labels' => ['english' => 'Religious administrative unit', 'german' => 'Religiöse Verwaltungseinheit'], 'parents' => ['AuthorityResource', 'CorporateBody'], 'identifier' => 'CorporateBody'],
            'ReligiousCorporateBody' => ['labels' => ['english' => 'Religious corporate body', 'german' => 'Religiöse Körperschaft'], 'parents' => ['AuthorityResource', 'CorporateBody'], 'identifier' => 'CorporateBody'],
            'CollectivePseudonym' => ['labels' => ['english' => 'Collective pseudonym', 'german' => 'Sammelpseudonym'], 'parents' => ['AuthorityResource', 'Person', 'DifferentiatedPerson'], 'identifier' => 'Person'],
            'Gods' => ['labels' => ['english' => 'Gods', 'german' => 'Götter'], 'parents' => ['AuthorityResource', 'Person', 'DifferentiatedPerson'], 'identifier' => 'Person'],
            'LiteraryOrLegendaryCharacter' => ['labels' => ['english' => 'Literary or legendary character', 'german' => 'Literarische oder Sagengestalt'], 'parents' => ['AuthorityResource', 'Person', 'DifferentiatedPerson'], 'identifier' => 'Person'],
            'Pseudonym' => ['labels' => ['english' => 'Pseudonym', 'german' => 'Pseudonym'], 'parents' => ['AuthorityResource', 'Person', 'DifferentiatedPerson'], 'identifier' => 'Person'],
            'RoyalOrMemberOfARoyalHouse' => ['labels' => ['english' => 'Royal or member of a royal house', 'german' => 'Regierender Fürst oder Mitglied eines regierenden Fürstenhauses'], 'parents' => ['AuthorityResource', 'Person', 'DifferentiatedPerson'], 'identifier' => 'Person'],
            'Spirits' => ['labels' => ['english' => 'Spirits', 'german' => 'Geister'], 'parents' => ['AuthorityResource', 'Person', 'DifferentiatedPerson'], 'identifier' => 'Person'],
            'PreferredNameOfThePerson' => ['labels' => ['english' => 'Preferred name of the person', 'german' => 'Bevorzugter Name der Person'], 'parents' => ['NameOfThePerson'], 'identifier' => 'NameOfThePerson'],
            'VariantNameOfThePerson' => ['labels' => ['english' => 'Variant name of the person', 'german' => 'Abweichender Name der Person'], 'parents' => ['NameOfThePerson'], 'identifier' => 'NameOfThePerson'],
            'DifferentiatedPerson' => ['labels' => ['english' => 'Differentiated person', 'german' => 'Individualisierte Person'], 'parents' => ['AuthorityResource', 'Person'], 'identifier' => 'Person'],
            'UndifferentiatedPerson' => ['labels' => ['english' => 'Undifferentiated person', 'german' => 'Nicht-individualisierte Person'], 'parents' => ['AuthorityResource', 'Person'], 'identifier' => 'Person'],
            'AdministrativeUnit' => ['labels' => ['english' => 'Administrative unit', 'german' => 'Verwaltungseinheit'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'BuildingOrMemorial' => ['labels' => ['english' => 'Building or memorial', 'german' => 'Bauwerk oder Denkmal'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'Country' => ['labels' => ['english' => 'Country', 'german' => 'Land oder Staat'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'ExtraterrestrialTerritory' => ['labels' => ['english' => 'Extraterrestrial territory', 'german' => 'Extraterrestrikum'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'FictivePlace' => ['labels' => ['english' => 'Fictive place', 'german' => 'Fiktiver Ort'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'MemberState' => ['labels' => ['english' => 'Member state', 'german' => 'Gliedstaat'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'NameOfSmallGeographicUnitLyingWithinAnotherGeographicUnit' => ['labels' => ['english' => 'Name of small geographic unit lying within another geographic unit', 'german' => 'Kleinräumiges Geografikum innerhalb eines Ortes'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'NaturalGeographicUnit' => ['labels' => ['english' => 'Natural geographic unit', 'german' => 'Natürlich geografische Einheit'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'ReligiousTerritory' => ['labels' => ['english' => 'Religious territory', 'german' => 'Religiöses Territorium'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'TerritorialCorporateBodyOrAdministrativeUnit' => ['labels' => ['english' => 'Territorial corporate body or administrative unit', 'german' => 'Gebietskörperschaft oder Verwaltungseinheit'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'WayBorderOrLine' => ['labels' => ['english' => 'Way, border or line', 'german' => 'Weg, Grenze oder Linie'], 'parents' => ['AuthorityResource', 'PlaceOrGeographicName'], 'identifier' => 'PlaceOrGeographicName'],
            'Fictive_term' => ['labels' => ['english' => 'Fictive term', 'german' => 'Fiktiver Sachbegriff'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'MeansOfTransportWithIndividual_name' => ['labels' => ['english' => 'Means of transport with individual name', 'german' => 'Verkehrsmittel mit Individualnamen'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'CharactersOrMorphemes' => ['labels' => ['english' => 'Characters or morphemes', 'german' => 'Buchstaben oder Morpheme'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'EthnographicName' => ['labels' => ['english' => 'Ethnographic name', 'german' => 'Ethnografikum'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'FictiveTerm' => ['labels' => ['english' => 'Fictive term', 'german' => 'Fiktiver Sachbegriff'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'GroupOfPersons' => ['labels' => ['english' => 'Group of persons', 'german' => 'Personengruppe'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'HistoricSingleEventOrEra' => ['labels' => ['english' => 'Historic single event or era', 'german' => 'Historisches Einzelereignis oder Epoche'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'Language' => ['labels' => ['english' => 'Language', 'german' => 'Sprache'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'MeansOfTransportWithIndividualName' => ['labels' => ['english' => 'Means of transport with individual name', 'german' => 'Verkehrsmittel mit Individualnamen'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'NomenclatureInBiologyOrChemistry' => ['labels' => ['english' => 'Nomenclature in biology or chemistry', 'german' => 'Nomenklatur Biologie - Chemie'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'ProductNameOrBrandName' => ['labels' => ['english' => 'Product name or brand name', 'german' => 'Produkt oder Markenname'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'SoftwareProduct' => ['labels' => ['english' => 'Software product', 'german' => 'Softwareprodukt'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'SubjectHeadingSensoStricto' => ['labels' => ['english' => 'Subject heading senso stricto', 'german' => 'Schlagwort senso stricto'], 'parents' => ['AuthorityResource', 'SubjectHeading'], 'identifier' => 'SubjectHeading'],
            'FullerFormOfNameOfThePerson' => ['labels' => ['english' => 'Fuller form of the name of the person', 'german' => 'Vollständiger Name der Person'], 'parents' => ['NameOfThePerson', 'VariantNameOfThePerson'], 'identifier' => 'NameOfThePerson'],
            'RealNameOfThePerson' => ['labels' => ['english' => 'Real name of the person', 'german' => 'Wirklicher Name der Person'], 'parents' => ['NameOfThePerson', 'VariantNameOfThePerson'], 'identifier' => 'NameOfThePerson'],
            'EarlierNameOfThePerson' => ['labels' => ['english' => 'Earlier name of the person', 'german' => 'Früherer Name der Person'], 'parents' => ['NameOfThePerson', 'VariantNameOfThePerson'], 'identifier' => 'NameOfThePerson'],
            'LaterNameOfThePerson' => ['labels' => ['english' => 'Later name of the person', 'german' => 'Späterer Name der Person'], 'parents' => ['NameOfThePerson', 'VariantNameOfThePerson'], 'identifier' => 'NameOfThePerson'],
            'PseudonymNameOfThePerson' => ['labels' => ['english' => 'Pseudonym name of the person', 'german' => 'Pseudonym der Person'], 'parents' => ['NameOfThePerson', 'VariantNameOfThePerson'], 'identifier' => 'NameOfThePerson'],
            'Collection' => ['labels' => ['english' => 'Collection', 'german' => 'Sammlung'], 'parents' => ['AuthorityResource', 'Work'], 'identifier' => 'Work'],
            'CollectiveManuscript' => ['labels' => ['english' => 'Collective manuscript', 'german' => 'Sammelhandschrift'], 'parents' => ['AuthorityResource', 'Work'], 'identifier' => 'Work'],
            'Expression' => ['labels' => ['english' => 'Expression', 'german' => 'Expression'], 'parents' => ['AuthorityResource', 'Work'], 'identifier' => 'Work'],
            'Manuscript' => ['labels' => ['english' => 'Manuscript', 'german' => 'Schriftdenkmal'], 'parents' => ['AuthorityResource', 'Work'], 'identifier' => 'Work'],
            'MusicalWork' => ['labels' => ['english' => 'Musical work', 'german' => 'Werk der Musik'], 'parents' => ['AuthorityResource', 'Work'], 'identifier' => 'Work'],
            'ProvenanceCharacteristic' => ['labels' => ['english' => 'Provenance characteristic', 'german' => 'Provenienzmerkmal'], 'parents' => ['AuthorityResource', 'Work'], 'identifier' => 'Work'],
            'VersionOfAMusicalWork' => ['labels' => ['english' => 'Version of a musical work', 'german' => 'Fassung eines Werks der Musik'], 'parents' => ['AuthorityResource', 'Work'], 'identifier' => 'Work'],
        ];


        //Implement parent
        function set_parameters()
        {
            $this->admin_script_args = [
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('oes_gnd_nonce'),
                'post_id' => true
            ];
            $this->frontend_script_args = [
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('oes_gnd_nonce_frontend')
            ];

            $this->search_options = [
                'label' => __('GND', 'oes'),
                'options' => [
                    [
                        'label' => '<label for="oes-gnd-type">' . __('Type', 'oes') . '</label>',
                        'form' => oes_html_get_form_element(
                            'select',
                            'oes-gnd-type',
                            'oes-gnd-type',
                            false,
                            ['options' => [
                                'all' => __('All', 'oes'),
                                'Person' => __('Person', 'oes'),
                                'ConferenceOrEvent' => __('Konferenz oder Veranstaltung', 'oes'),
                                'Work' => __('Werk', 'oes'),
                                'CorporateBody' => __('Körperschaft', 'oes'),
                                'SubjectHeading' => __('Schlagwort', 'oes'),
                                'PlaceOrGeographicName' => __('Geografikum', 'oes'),
                                'Familie' => __('Familie', 'oes')
                            ]])
                    ],
                    /*[
                        'label' => '<label for="oes-gnd-field">' . __('Field', 'oes') . '</label>',
                        'form' => oes_html_get_form_element(
                            'select',
                            'oes-gnd-field',
                            'oes-gnd-field',
                            false,
                            ['options' => [
                                'any' => __('Any', 'oes'),
                                'preferredName' => __('Preferred Name', 'oes')
                            ]])
                    ],*/
                    [
                        'label' => '<label for="oes-gnd-size">' . __('Size', 'oes') . '</label>',
                        'form' => oes_html_get_form_element(
                            'number',
                            'oes-gnd-size',
                            'oes-gnd-size',
                            5,
                            ['min' => 0, 'max' => 100])
                    ]
                ]
            ];

            /* prepare options for admin configuration : loop through all properties and store as option */
            $properties = [];
            foreach (self::PROPERTIES as $key => $property)
                $properties[$key] = ($property['label']['german'] ?? '[Label missing]') . '/' .
                    ($property['label']['english'] ?? '[Label missing]') . ' (' . $key . ')';
            asort($properties);

            $this->config_options = [
                'properties' => [
                    'label' => 'GND Properties',
                    'type' => 'select',
                    'multiple' => true,
                    'capabilities' => ['backend', 'fields', 'lod'],
                    'skip_admin_config' => true,
                    'options' => [
                        'gnd' => [
                            'label' => 'GND Basic',
                            'options' => [
                                'base-timestamp' => 'GND Timestamp'
                            ]
                        ],
                        'gnd_properties' => [
                            'label' => 'GND Properties',
                            'options' => $properties
                        ]
                    ],
                    'description' => 'Enable copying from GND entry to field. Only relevant if LOD Options "Copy To Post" is set.'
                ]
            ];

        }
    }

    /* include gnd api files and instantiate api interface */
    oes_include('/includes/api/gnd/rest-api-lobid.php');
    oes_include('/includes/api/gnd/admin.php');
    oes_include('/includes/api/gnd/frontend.php');
    OES()->apis['gnd'] = new GND_Interface('gnd');
}