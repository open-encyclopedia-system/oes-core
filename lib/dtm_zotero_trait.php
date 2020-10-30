<?php
/*
 * This file is part of OES, the Open Encyclopedia System.
 *
 * Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>

<?php

/**
 * Trait dtm_zotero_trait
 * @property $zot_itemType
 * @property $zot_itemType__html
 * @property $zot_itemType__float
 * @property $zot_itemType__int
 * @property $zot_itemType__id
 * @property $zot_itemType__ids
 * @property $zot_itemType__objs
 * @property $zot_itemType__obj
 * @property $zot_itemType__terms
 * @property $zot_itemType__term
 * @property $zot_itemType__array
 *
 * @property $zot_title
 * @property $zot_title__html
 * @property $zot_title__float
 * @property $zot_title__int
 * @property $zot_title__id
 * @property $zot_title__ids
 * @property $zot_title__objs
 * @property $zot_title__obj
 * @property $zot_title__terms
 * @property $zot_title__term
 * @property $zot_title__array
 *
 * @property $zot_bookTitle
 * @property $zot_bookTitle__html
 * @property $zot_bookTitle__float
 * @property $zot_bookTitle__int
 * @property $zot_bookTitle__id
 * @property $zot_bookTitle__ids
 * @property $zot_bookTitle__objs
 * @property $zot_bookTitle__obj
 * @property $zot_bookTitle__terms
 * @property $zot_bookTitle__term
 * @property $zot_bookTitle__array
 *
 * @property $zot_creators
 * @property $zot_creators__html
 * @property $zot_creators__float
 * @property $zot_creators__int
 * @property $zot_creators__id
 * @property $zot_creators__ids
 * @property $zot_creators__objs
 * @property $zot_creators__obj
 * @property $zot_creators__terms
 * @property $zot_creators__term
 * @property $zot_creators__array
 *
 * @property $zot_series
 * @property $zot_series__html
 * @property $zot_series__float
 * @property $zot_series__int
 * @property $zot_series__id
 * @property $zot_series__ids
 * @property $zot_series__objs
 * @property $zot_series__obj
 * @property $zot_series__terms
 * @property $zot_series__term
 * @property $zot_series__array
 *
 * @property $zot_seriesNumber
 * @property $zot_seriesNumber__html
 * @property $zot_seriesNumber__float
 * @property $zot_seriesNumber__int
 * @property $zot_seriesNumber__id
 * @property $zot_seriesNumber__ids
 * @property $zot_seriesNumber__objs
 * @property $zot_seriesNumber__obj
 * @property $zot_seriesNumber__terms
 * @property $zot_seriesNumber__term
 * @property $zot_seriesNumber__array
 *
 * @property $zot_volume
 * @property $zot_volume__html
 * @property $zot_volume__float
 * @property $zot_volume__int
 * @property $zot_volume__id
 * @property $zot_volume__ids
 * @property $zot_volume__objs
 * @property $zot_volume__obj
 * @property $zot_volume__terms
 * @property $zot_volume__term
 * @property $zot_volume__array
 *
 * @property $zot_numberOfVolumes
 * @property $zot_numberOfVolumes__html
 * @property $zot_numberOfVolumes__float
 * @property $zot_numberOfVolumes__int
 * @property $zot_numberOfVolumes__id
 * @property $zot_numberOfVolumes__ids
 * @property $zot_numberOfVolumes__objs
 * @property $zot_numberOfVolumes__obj
 * @property $zot_numberOfVolumes__terms
 * @property $zot_numberOfVolumes__term
 * @property $zot_numberOfVolumes__array
 *
 * @property $zot_edition
 * @property $zot_edition__html
 * @property $zot_edition__float
 * @property $zot_edition__int
 * @property $zot_edition__id
 * @property $zot_edition__ids
 * @property $zot_edition__objs
 * @property $zot_edition__obj
 * @property $zot_edition__terms
 * @property $zot_edition__term
 * @property $zot_edition__array
 *
 * @property $zot_place
 * @property $zot_place__html
 * @property $zot_place__float
 * @property $zot_place__int
 * @property $zot_place__id
 * @property $zot_place__ids
 * @property $zot_place__objs
 * @property $zot_place__obj
 * @property $zot_place__terms
 * @property $zot_place__term
 * @property $zot_place__array
 *
 * @property $zot_publisher
 * @property $zot_publisher__html
 * @property $zot_publisher__float
 * @property $zot_publisher__int
 * @property $zot_publisher__id
 * @property $zot_publisher__ids
 * @property $zot_publisher__objs
 * @property $zot_publisher__obj
 * @property $zot_publisher__terms
 * @property $zot_publisher__term
 * @property $zot_publisher__array
 *
 * @property $zot_date
 * @property $zot_date__html
 * @property $zot_date__float
 * @property $zot_date__int
 * @property $zot_date__id
 * @property $zot_date__ids
 * @property $zot_date__objs
 * @property $zot_date__obj
 * @property $zot_date__terms
 * @property $zot_date__term
 * @property $zot_date__array
 *
 * @property $zot_pages
 * @property $zot_pages__html
 * @property $zot_pages__float
 * @property $zot_pages__int
 * @property $zot_pages__id
 * @property $zot_pages__ids
 * @property $zot_pages__objs
 * @property $zot_pages__obj
 * @property $zot_pages__terms
 * @property $zot_pages__term
 * @property $zot_pages__array
 *
 * @property $zot_language
 * @property $zot_language__html
 * @property $zot_language__float
 * @property $zot_language__int
 * @property $zot_language__id
 * @property $zot_language__ids
 * @property $zot_language__objs
 * @property $zot_language__obj
 * @property $zot_language__terms
 * @property $zot_language__term
 * @property $zot_language__array
 *
 * @property $zot_caseName
 * @property $zot_caseName__html
 * @property $zot_caseName__float
 * @property $zot_caseName__int
 * @property $zot_caseName__id
 * @property $zot_caseName__ids
 * @property $zot_caseName__objs
 * @property $zot_caseName__obj
 * @property $zot_caseName__terms
 * @property $zot_caseName__term
 * @property $zot_caseName__array
 *
 * @property $zot_nameOfAct
 * @property $zot_nameOfAct__html
 * @property $zot_nameOfAct__float
 * @property $zot_nameOfAct__int
 * @property $zot_nameOfAct__id
 * @property $zot_nameOfAct__ids
 * @property $zot_nameOfAct__objs
 * @property $zot_nameOfAct__obj
 * @property $zot_nameOfAct__terms
 * @property $zot_nameOfAct__term
 * @property $zot_nameOfAct__array
 *
 * @property $zot_subject
 * @property $zot_subject__html
 * @property $zot_subject__float
 * @property $zot_subject__int
 * @property $zot_subject__id
 * @property $zot_subject__ids
 * @property $zot_subject__objs
 * @property $zot_subject__obj
 * @property $zot_subject__terms
 * @property $zot_subject__term
 * @property $zot_subject__array
 *
 * @property $zot_dictionaryTitle
 * @property $zot_dictionaryTitle__html
 * @property $zot_dictionaryTitle__float
 * @property $zot_dictionaryTitle__int
 * @property $zot_dictionaryTitle__id
 * @property $zot_dictionaryTitle__ids
 * @property $zot_dictionaryTitle__objs
 * @property $zot_dictionaryTitle__obj
 * @property $zot_dictionaryTitle__terms
 * @property $zot_dictionaryTitle__term
 * @property $zot_dictionaryTitle__array
 *
 * @property $zot_programTitle
 * @property $zot_programTitle__html
 * @property $zot_programTitle__float
 * @property $zot_programTitle__int
 * @property $zot_programTitle__id
 * @property $zot_programTitle__ids
 * @property $zot_programTitle__objs
 * @property $zot_programTitle__obj
 * @property $zot_programTitle__terms
 * @property $zot_programTitle__term
 * @property $zot_programTitle__array
 *
 * @property $zot_blogTitle
 * @property $zot_blogTitle__html
 * @property $zot_blogTitle__float
 * @property $zot_blogTitle__int
 * @property $zot_blogTitle__id
 * @property $zot_blogTitle__ids
 * @property $zot_blogTitle__objs
 * @property $zot_blogTitle__obj
 * @property $zot_blogTitle__terms
 * @property $zot_blogTitle__term
 * @property $zot_blogTitle__array
 *
 * @property $zot_code
 * @property $zot_code__html
 * @property $zot_code__float
 * @property $zot_code__int
 * @property $zot_code__id
 * @property $zot_code__ids
 * @property $zot_code__objs
 * @property $zot_code__obj
 * @property $zot_code__terms
 * @property $zot_code__term
 * @property $zot_code__array
 *
 * @property $zot_reportNumber
 * @property $zot_reportNumber__html
 * @property $zot_reportNumber__float
 * @property $zot_reportNumber__int
 * @property $zot_reportNumber__id
 * @property $zot_reportNumber__ids
 * @property $zot_reportNumber__objs
 * @property $zot_reportNumber__obj
 * @property $zot_reportNumber__terms
 * @property $zot_reportNumber__term
 * @property $zot_reportNumber__array
 *
 * @property $zot_reporter
 * @property $zot_reporter__html
 * @property $zot_reporter__float
 * @property $zot_reporter__int
 * @property $zot_reporter__id
 * @property $zot_reporter__ids
 * @property $zot_reporter__objs
 * @property $zot_reporter__obj
 * @property $zot_reporter__terms
 * @property $zot_reporter__term
 * @property $zot_reporter__array
 *
 * @property $zot_distributor
 * @property $zot_distributor__html
 * @property $zot_distributor__float
 * @property $zot_distributor__int
 * @property $zot_distributor__id
 * @property $zot_distributor__ids
 * @property $zot_distributor__objs
 * @property $zot_distributor__obj
 * @property $zot_distributor__terms
 * @property $zot_distributor__term
 * @property $zot_distributor__array
 *
 * @property $zot_presentationType
 * @property $zot_presentationType__html
 * @property $zot_presentationType__float
 * @property $zot_presentationType__int
 * @property $zot_presentationType__id
 * @property $zot_presentationType__ids
 * @property $zot_presentationType__objs
 * @property $zot_presentationType__obj
 * @property $zot_presentationType__terms
 * @property $zot_presentationType__term
 * @property $zot_presentationType__array
 *
 * @property $zot_letterType
 * @property $zot_letterType__html
 * @property $zot_letterType__float
 * @property $zot_letterType__int
 * @property $zot_letterType__id
 * @property $zot_letterType__ids
 * @property $zot_letterType__objs
 * @property $zot_letterType__obj
 * @property $zot_letterType__terms
 * @property $zot_letterType__term
 * @property $zot_letterType__array
 *
 * @property $zot_manuscriptType
 * @property $zot_manuscriptType__html
 * @property $zot_manuscriptType__float
 * @property $zot_manuscriptType__int
 * @property $zot_manuscriptType__id
 * @property $zot_manuscriptType__ids
 * @property $zot_manuscriptType__objs
 * @property $zot_manuscriptType__obj
 * @property $zot_manuscriptType__terms
 * @property $zot_manuscriptType__term
 * @property $zot_manuscriptType__array
 *
 * @property $zot_mapType
 * @property $zot_mapType__html
 * @property $zot_mapType__float
 * @property $zot_mapType__int
 * @property $zot_mapType__id
 * @property $zot_mapType__ids
 * @property $zot_mapType__objs
 * @property $zot_mapType__obj
 * @property $zot_mapType__terms
 * @property $zot_mapType__term
 * @property $zot_mapType__array
 *
 * @property $zot_publicationTitle
 * @property $zot_publicationTitle__html
 * @property $zot_publicationTitle__float
 * @property $zot_publicationTitle__int
 * @property $zot_publicationTitle__id
 * @property $zot_publicationTitle__ids
 * @property $zot_publicationTitle__objs
 * @property $zot_publicationTitle__obj
 * @property $zot_publicationTitle__terms
 * @property $zot_publicationTitle__term
 * @property $zot_publicationTitle__array
 *
 * @property $zot_committee
 * @property $zot_committee__html
 * @property $zot_committee__float
 * @property $zot_committee__int
 * @property $zot_committee__id
 * @property $zot_committee__ids
 * @property $zot_committee__objs
 * @property $zot_committee__obj
 * @property $zot_committee__terms
 * @property $zot_committee__term
 * @property $zot_committee__array
 *
 * @property $zot_billNumber
 * @property $zot_billNumber__html
 * @property $zot_billNumber__float
 * @property $zot_billNumber__int
 * @property $zot_billNumber__id
 * @property $zot_billNumber__ids
 * @property $zot_billNumber__objs
 * @property $zot_billNumber__obj
 * @property $zot_billNumber__terms
 * @property $zot_billNumber__term
 * @property $zot_billNumber__array
 *
 * @property $zot_videoRecordingFormat
 * @property $zot_videoRecordingFormat__html
 * @property $zot_videoRecordingFormat__float
 * @property $zot_videoRecordingFormat__int
 * @property $zot_videoRecordingFormat__id
 * @property $zot_videoRecordingFormat__ids
 * @property $zot_videoRecordingFormat__objs
 * @property $zot_videoRecordingFormat__obj
 * @property $zot_videoRecordingFormat__terms
 * @property $zot_videoRecordingFormat__term
 * @property $zot_videoRecordingFormat__array
 *
 * @property $zot_forumTitle
 * @property $zot_forumTitle__html
 * @property $zot_forumTitle__float
 * @property $zot_forumTitle__int
 * @property $zot_forumTitle__id
 * @property $zot_forumTitle__ids
 * @property $zot_forumTitle__objs
 * @property $zot_forumTitle__obj
 * @property $zot_forumTitle__terms
 * @property $zot_forumTitle__term
 * @property $zot_forumTitle__array
 *
 * @property $zot_encyclopediaTitle
 * @property $zot_encyclopediaTitle__html
 * @property $zot_encyclopediaTitle__float
 * @property $zot_encyclopediaTitle__int
 * @property $zot_encyclopediaTitle__id
 * @property $zot_encyclopediaTitle__ids
 * @property $zot_encyclopediaTitle__objs
 * @property $zot_encyclopediaTitle__obj
 * @property $zot_encyclopediaTitle__terms
 * @property $zot_encyclopediaTitle__term
 * @property $zot_encyclopediaTitle__array
 *
 * @property $zot_thesisType
 * @property $zot_thesisType__html
 * @property $zot_thesisType__float
 * @property $zot_thesisType__int
 * @property $zot_thesisType__id
 * @property $zot_thesisType__ids
 * @property $zot_thesisType__objs
 * @property $zot_thesisType__obj
 * @property $zot_thesisType__terms
 * @property $zot_thesisType__term
 * @property $zot_thesisType__array
 *
 * @property $zot_artworkMedium
 * @property $zot_artworkMedium__html
 * @property $zot_artworkMedium__float
 * @property $zot_artworkMedium__int
 * @property $zot_artworkMedium__id
 * @property $zot_artworkMedium__ids
 * @property $zot_artworkMedium__objs
 * @property $zot_artworkMedium__obj
 * @property $zot_artworkMedium__terms
 * @property $zot_artworkMedium__term
 * @property $zot_artworkMedium__array
 *
 * @property $zot_websiteTitle
 * @property $zot_websiteTitle__html
 * @property $zot_websiteTitle__float
 * @property $zot_websiteTitle__int
 * @property $zot_websiteTitle__id
 * @property $zot_websiteTitle__ids
 * @property $zot_websiteTitle__objs
 * @property $zot_websiteTitle__obj
 * @property $zot_websiteTitle__terms
 * @property $zot_websiteTitle__term
 * @property $zot_websiteTitle__array
 *
 * @property $zot_country
 * @property $zot_country__html
 * @property $zot_country__float
 * @property $zot_country__int
 * @property $zot_country__id
 * @property $zot_country__ids
 * @property $zot_country__objs
 * @property $zot_country__obj
 * @property $zot_country__terms
 * @property $zot_country__term
 * @property $zot_country__array
 *
 * @property $zot_proceedingsTitle
 * @property $zot_proceedingsTitle__html
 * @property $zot_proceedingsTitle__float
 * @property $zot_proceedingsTitle__int
 * @property $zot_proceedingsTitle__id
 * @property $zot_proceedingsTitle__ids
 * @property $zot_proceedingsTitle__objs
 * @property $zot_proceedingsTitle__obj
 * @property $zot_proceedingsTitle__terms
 * @property $zot_proceedingsTitle__term
 * @property $zot_proceedingsTitle__array
 *
 * @property $zot_versionNumber
 * @property $zot_versionNumber__html
 * @property $zot_versionNumber__float
 * @property $zot_versionNumber__int
 * @property $zot_versionNumber__id
 * @property $zot_versionNumber__ids
 * @property $zot_versionNumber__objs
 * @property $zot_versionNumber__obj
 * @property $zot_versionNumber__terms
 * @property $zot_versionNumber__term
 * @property $zot_versionNumber__array
 *
 * @property $zot_reporterVolume
 * @property $zot_reporterVolume__html
 * @property $zot_reporterVolume__float
 * @property $zot_reporterVolume__int
 * @property $zot_reporterVolume__id
 * @property $zot_reporterVolume__ids
 * @property $zot_reporterVolume__objs
 * @property $zot_reporterVolume__obj
 * @property $zot_reporterVolume__terms
 * @property $zot_reporterVolume__term
 * @property $zot_reporterVolume__array
 *
 * @property $zot_university
 * @property $zot_university__html
 * @property $zot_university__float
 * @property $zot_university__int
 * @property $zot_university__id
 * @property $zot_university__ids
 * @property $zot_university__objs
 * @property $zot_university__obj
 * @property $zot_university__terms
 * @property $zot_university__term
 * @property $zot_university__array
 *
 * @property $zot_postType
 * @property $zot_postType__html
 * @property $zot_postType__float
 * @property $zot_postType__int
 * @property $zot_postType__id
 * @property $zot_postType__ids
 * @property $zot_postType__objs
 * @property $zot_postType__obj
 * @property $zot_postType__terms
 * @property $zot_postType__term
 * @property $zot_postType__array
 *
 * @property $zot_artworkSize
 * @property $zot_artworkSize__html
 * @property $zot_artworkSize__float
 * @property $zot_artworkSize__int
 * @property $zot_artworkSize__id
 * @property $zot_artworkSize__ids
 * @property $zot_artworkSize__objs
 * @property $zot_artworkSize__obj
 * @property $zot_artworkSize__terms
 * @property $zot_artworkSize__term
 * @property $zot_artworkSize__array
 *
 * @property $zot_episodeNumber
 * @property $zot_episodeNumber__html
 * @property $zot_episodeNumber__float
 * @property $zot_episodeNumber__int
 * @property $zot_episodeNumber__id
 * @property $zot_episodeNumber__ids
 * @property $zot_episodeNumber__objs
 * @property $zot_episodeNumber__obj
 * @property $zot_episodeNumber__terms
 * @property $zot_episodeNumber__term
 * @property $zot_episodeNumber__array
 *
 * @property $zot_seriesTitle
 * @property $zot_seriesTitle__html
 * @property $zot_seriesTitle__float
 * @property $zot_seriesTitle__int
 * @property $zot_seriesTitle__id
 * @property $zot_seriesTitle__ids
 * @property $zot_seriesTitle__objs
 * @property $zot_seriesTitle__obj
 * @property $zot_seriesTitle__terms
 * @property $zot_seriesTitle__term
 * @property $zot_seriesTitle__array
 *
 * @property $zot_interviewMedium
 * @property $zot_interviewMedium__html
 * @property $zot_interviewMedium__float
 * @property $zot_interviewMedium__int
 * @property $zot_interviewMedium__id
 * @property $zot_interviewMedium__ids
 * @property $zot_interviewMedium__objs
 * @property $zot_interviewMedium__obj
 * @property $zot_interviewMedium__terms
 * @property $zot_interviewMedium__term
 * @property $zot_interviewMedium__array
 *
 * @property $zot_scale
 * @property $zot_scale__html
 * @property $zot_scale__float
 * @property $zot_scale__int
 * @property $zot_scale__id
 * @property $zot_scale__ids
 * @property $zot_scale__objs
 * @property $zot_scale__obj
 * @property $zot_scale__terms
 * @property $zot_scale__term
 * @property $zot_scale__array
 *
 * @property $zot_codeNumber
 * @property $zot_codeNumber__html
 * @property $zot_codeNumber__float
 * @property $zot_codeNumber__int
 * @property $zot_codeNumber__id
 * @property $zot_codeNumber__ids
 * @property $zot_codeNumber__objs
 * @property $zot_codeNumber__obj
 * @property $zot_codeNumber__terms
 * @property $zot_codeNumber__term
 * @property $zot_codeNumber__array
 *
 * @property $zot_reportType
 * @property $zot_reportType__html
 * @property $zot_reportType__float
 * @property $zot_reportType__int
 * @property $zot_reportType__id
 * @property $zot_reportType__ids
 * @property $zot_reportType__objs
 * @property $zot_reportType__obj
 * @property $zot_reportType__terms
 * @property $zot_reportType__term
 * @property $zot_reportType__array
 *
 * @property $zot_websiteType
 * @property $zot_websiteType__html
 * @property $zot_websiteType__float
 * @property $zot_websiteType__int
 * @property $zot_websiteType__id
 * @property $zot_websiteType__ids
 * @property $zot_websiteType__objs
 * @property $zot_websiteType__obj
 * @property $zot_websiteType__terms
 * @property $zot_websiteType__term
 * @property $zot_websiteType__array
 *
 * @property $zot_audioFileType
 * @property $zot_audioFileType__html
 * @property $zot_audioFileType__float
 * @property $zot_audioFileType__int
 * @property $zot_audioFileType__id
 * @property $zot_audioFileType__ids
 * @property $zot_audioFileType__objs
 * @property $zot_audioFileType__obj
 * @property $zot_audioFileType__terms
 * @property $zot_audioFileType__term
 * @property $zot_audioFileType__array
 *
 * @property $zot_issue
 * @property $zot_issue__html
 * @property $zot_issue__float
 * @property $zot_issue__int
 * @property $zot_issue__id
 * @property $zot_issue__ids
 * @property $zot_issue__objs
 * @property $zot_issue__obj
 * @property $zot_issue__terms
 * @property $zot_issue__term
 * @property $zot_issue__array
 *
 * @property $zot_conferenceName
 * @property $zot_conferenceName__html
 * @property $zot_conferenceName__float
 * @property $zot_conferenceName__int
 * @property $zot_conferenceName__id
 * @property $zot_conferenceName__ids
 * @property $zot_conferenceName__objs
 * @property $zot_conferenceName__obj
 * @property $zot_conferenceName__terms
 * @property $zot_conferenceName__term
 * @property $zot_conferenceName__array
 *
 * @property $zot_publicLawNumber
 * @property $zot_publicLawNumber__html
 * @property $zot_publicLawNumber__float
 * @property $zot_publicLawNumber__int
 * @property $zot_publicLawNumber__id
 * @property $zot_publicLawNumber__ids
 * @property $zot_publicLawNumber__objs
 * @property $zot_publicLawNumber__obj
 * @property $zot_publicLawNumber__terms
 * @property $zot_publicLawNumber__term
 * @property $zot_publicLawNumber__array
 *
 * @property $zot_audioRecordingFormat
 * @property $zot_audioRecordingFormat__html
 * @property $zot_audioRecordingFormat__float
 * @property $zot_audioRecordingFormat__int
 * @property $zot_audioRecordingFormat__id
 * @property $zot_audioRecordingFormat__ids
 * @property $zot_audioRecordingFormat__objs
 * @property $zot_audioRecordingFormat__obj
 * @property $zot_audioRecordingFormat__terms
 * @property $zot_audioRecordingFormat__term
 * @property $zot_audioRecordingFormat__array
 *
 * @property $zot_assignee
 * @property $zot_assignee__html
 * @property $zot_assignee__float
 * @property $zot_assignee__int
 * @property $zot_assignee__id
 * @property $zot_assignee__ids
 * @property $zot_assignee__objs
 * @property $zot_assignee__obj
 * @property $zot_assignee__terms
 * @property $zot_assignee__term
 * @property $zot_assignee__array
 *
 * @property $zot_genre
 * @property $zot_genre__html
 * @property $zot_genre__float
 * @property $zot_genre__int
 * @property $zot_genre__id
 * @property $zot_genre__ids
 * @property $zot_genre__objs
 * @property $zot_genre__obj
 * @property $zot_genre__terms
 * @property $zot_genre__term
 * @property $zot_genre__array
 *
 * @property $zot_codeVolume
 * @property $zot_codeVolume__html
 * @property $zot_codeVolume__float
 * @property $zot_codeVolume__int
 * @property $zot_codeVolume__id
 * @property $zot_codeVolume__ids
 * @property $zot_codeVolume__objs
 * @property $zot_codeVolume__obj
 * @property $zot_codeVolume__terms
 * @property $zot_codeVolume__term
 * @property $zot_codeVolume__array
 *
 * @property $zot_court
 * @property $zot_court__html
 * @property $zot_court__float
 * @property $zot_court__int
 * @property $zot_court__id
 * @property $zot_court__ids
 * @property $zot_court__objs
 * @property $zot_court__obj
 * @property $zot_court__terms
 * @property $zot_court__term
 * @property $zot_court__array
 *
 * @property $zot_issuingAuthority
 * @property $zot_issuingAuthority__html
 * @property $zot_issuingAuthority__float
 * @property $zot_issuingAuthority__int
 * @property $zot_issuingAuthority__id
 * @property $zot_issuingAuthority__ids
 * @property $zot_issuingAuthority__objs
 * @property $zot_issuingAuthority__obj
 * @property $zot_issuingAuthority__terms
 * @property $zot_issuingAuthority__term
 * @property $zot_issuingAuthority__array
 *
 * @property $zot_dateEnacted
 * @property $zot_dateEnacted__html
 * @property $zot_dateEnacted__float
 * @property $zot_dateEnacted__int
 * @property $zot_dateEnacted__id
 * @property $zot_dateEnacted__ids
 * @property $zot_dateEnacted__objs
 * @property $zot_dateEnacted__obj
 * @property $zot_dateEnacted__terms
 * @property $zot_dateEnacted__term
 * @property $zot_dateEnacted__array
 *
 * @property $zot_meetingName
 * @property $zot_meetingName__html
 * @property $zot_meetingName__float
 * @property $zot_meetingName__int
 * @property $zot_meetingName__id
 * @property $zot_meetingName__ids
 * @property $zot_meetingName__objs
 * @property $zot_meetingName__obj
 * @property $zot_meetingName__terms
 * @property $zot_meetingName__term
 * @property $zot_meetingName__array
 *
 * @property $zot_system
 * @property $zot_system__html
 * @property $zot_system__float
 * @property $zot_system__int
 * @property $zot_system__id
 * @property $zot_system__ids
 * @property $zot_system__objs
 * @property $zot_system__obj
 * @property $zot_system__terms
 * @property $zot_system__term
 * @property $zot_system__array
 *
 * @property $zot_docketNumber
 * @property $zot_docketNumber__html
 * @property $zot_docketNumber__float
 * @property $zot_docketNumber__int
 * @property $zot_docketNumber__id
 * @property $zot_docketNumber__ids
 * @property $zot_docketNumber__objs
 * @property $zot_docketNumber__obj
 * @property $zot_docketNumber__terms
 * @property $zot_docketNumber__term
 * @property $zot_docketNumber__array
 *
 * @property $zot_firstPage
 * @property $zot_firstPage__html
 * @property $zot_firstPage__float
 * @property $zot_firstPage__int
 * @property $zot_firstPage__id
 * @property $zot_firstPage__ids
 * @property $zot_firstPage__objs
 * @property $zot_firstPage__obj
 * @property $zot_firstPage__terms
 * @property $zot_firstPage__term
 * @property $zot_firstPage__array
 *
 * @property $zot_codePages
 * @property $zot_codePages__html
 * @property $zot_codePages__float
 * @property $zot_codePages__int
 * @property $zot_codePages__id
 * @property $zot_codePages__ids
 * @property $zot_codePages__objs
 * @property $zot_codePages__obj
 * @property $zot_codePages__terms
 * @property $zot_codePages__term
 * @property $zot_codePages__array
 *
 * @property $zot_numPages
 * @property $zot_numPages__html
 * @property $zot_numPages__float
 * @property $zot_numPages__int
 * @property $zot_numPages__id
 * @property $zot_numPages__ids
 * @property $zot_numPages__objs
 * @property $zot_numPages__obj
 * @property $zot_numPages__terms
 * @property $zot_numPages__term
 * @property $zot_numPages__array
 *
 * @property $zot_patentNumber
 * @property $zot_patentNumber__html
 * @property $zot_patentNumber__float
 * @property $zot_patentNumber__int
 * @property $zot_patentNumber__id
 * @property $zot_patentNumber__ids
 * @property $zot_patentNumber__objs
 * @property $zot_patentNumber__obj
 * @property $zot_patentNumber__terms
 * @property $zot_patentNumber__term
 * @property $zot_patentNumber__array
 *
 * @property $zot_documentNumber
 * @property $zot_documentNumber__html
 * @property $zot_documentNumber__float
 * @property $zot_documentNumber__int
 * @property $zot_documentNumber__id
 * @property $zot_documentNumber__ids
 * @property $zot_documentNumber__objs
 * @property $zot_documentNumber__obj
 * @property $zot_documentNumber__terms
 * @property $zot_documentNumber__term
 * @property $zot_documentNumber__array
 *
 * @property $zot_institution
 * @property $zot_institution__html
 * @property $zot_institution__float
 * @property $zot_institution__int
 * @property $zot_institution__id
 * @property $zot_institution__ids
 * @property $zot_institution__objs
 * @property $zot_institution__obj
 * @property $zot_institution__terms
 * @property $zot_institution__term
 * @property $zot_institution__array
 *
 * @property $zot_network
 * @property $zot_network__html
 * @property $zot_network__float
 * @property $zot_network__int
 * @property $zot_network__id
 * @property $zot_network__ids
 * @property $zot_network__objs
 * @property $zot_network__obj
 * @property $zot_network__terms
 * @property $zot_network__term
 * @property $zot_network__array
 *
 * @property $zot_url
 * @property $zot_url__html
 * @property $zot_url__float
 * @property $zot_url__int
 * @property $zot_url__id
 * @property $zot_url__ids
 * @property $zot_url__objs
 * @property $zot_url__obj
 * @property $zot_url__terms
 * @property $zot_url__term
 * @property $zot_url__array
 *
 * @property $zot_accessDate
 * @property $zot_accessDate__html
 * @property $zot_accessDate__float
 * @property $zot_accessDate__int
 * @property $zot_accessDate__id
 * @property $zot_accessDate__ids
 * @property $zot_accessDate__objs
 * @property $zot_accessDate__obj
 * @property $zot_accessDate__terms
 * @property $zot_accessDate__term
 * @property $zot_accessDate__array
 *
 * @property $zot_label
 * @property $zot_label__html
 * @property $zot_label__float
 * @property $zot_label__int
 * @property $zot_label__id
 * @property $zot_label__ids
 * @property $zot_label__objs
 * @property $zot_label__obj
 * @property $zot_label__terms
 * @property $zot_label__term
 * @property $zot_label__array
 *
 * @property $zot_studio
 * @property $zot_studio__html
 * @property $zot_studio__float
 * @property $zot_studio__int
 * @property $zot_studio__id
 * @property $zot_studio__ids
 * @property $zot_studio__objs
 * @property $zot_studio__obj
 * @property $zot_studio__terms
 * @property $zot_studio__term
 * @property $zot_studio__array
 *
 * @property $zot_filingDate
 * @property $zot_filingDate__html
 * @property $zot_filingDate__float
 * @property $zot_filingDate__int
 * @property $zot_filingDate__id
 * @property $zot_filingDate__ids
 * @property $zot_filingDate__objs
 * @property $zot_filingDate__obj
 * @property $zot_filingDate__terms
 * @property $zot_filingDate__term
 * @property $zot_filingDate__array
 *
 * @property $zot_company
 * @property $zot_company__html
 * @property $zot_company__float
 * @property $zot_company__int
 * @property $zot_company__id
 * @property $zot_company__ids
 * @property $zot_company__objs
 * @property $zot_company__obj
 * @property $zot_company__terms
 * @property $zot_company__term
 * @property $zot_company__array
 *
 * @property $zot_section
 * @property $zot_section__html
 * @property $zot_section__float
 * @property $zot_section__int
 * @property $zot_section__id
 * @property $zot_section__ids
 * @property $zot_section__objs
 * @property $zot_section__obj
 * @property $zot_section__terms
 * @property $zot_section__term
 * @property $zot_section__array
 *
 * @property $zot_programmingLanguage
 * @property $zot_programmingLanguage__html
 * @property $zot_programmingLanguage__float
 * @property $zot_programmingLanguage__int
 * @property $zot_programmingLanguage__id
 * @property $zot_programmingLanguage__ids
 * @property $zot_programmingLanguage__objs
 * @property $zot_programmingLanguage__obj
 * @property $zot_programmingLanguage__terms
 * @property $zot_programmingLanguage__term
 * @property $zot_programmingLanguage__array
 *
 * @property $zot_dateDecided
 * @property $zot_dateDecided__html
 * @property $zot_dateDecided__float
 * @property $zot_dateDecided__int
 * @property $zot_dateDecided__id
 * @property $zot_dateDecided__ids
 * @property $zot_dateDecided__objs
 * @property $zot_dateDecided__obj
 * @property $zot_dateDecided__terms
 * @property $zot_dateDecided__term
 * @property $zot_dateDecided__array
 *
 * @property $zot_session
 * @property $zot_session__html
 * @property $zot_session__float
 * @property $zot_session__int
 * @property $zot_session__id
 * @property $zot_session__ids
 * @property $zot_session__objs
 * @property $zot_session__obj
 * @property $zot_session__terms
 * @property $zot_session__term
 * @property $zot_session__array
 *
 * @property $zot_legislativeBody
 * @property $zot_legislativeBody__html
 * @property $zot_legislativeBody__float
 * @property $zot_legislativeBody__int
 * @property $zot_legislativeBody__id
 * @property $zot_legislativeBody__ids
 * @property $zot_legislativeBody__objs
 * @property $zot_legislativeBody__obj
 * @property $zot_legislativeBody__terms
 * @property $zot_legislativeBody__term
 * @property $zot_legislativeBody__array
 *
 * @property $zot_applicationNumber
 * @property $zot_applicationNumber__html
 * @property $zot_applicationNumber__float
 * @property $zot_applicationNumber__int
 * @property $zot_applicationNumber__id
 * @property $zot_applicationNumber__ids
 * @property $zot_applicationNumber__objs
 * @property $zot_applicationNumber__obj
 * @property $zot_applicationNumber__terms
 * @property $zot_applicationNumber__term
 * @property $zot_applicationNumber__array
 *
 * @property $zot_runningTime
 * @property $zot_runningTime__html
 * @property $zot_runningTime__float
 * @property $zot_runningTime__int
 * @property $zot_runningTime__id
 * @property $zot_runningTime__ids
 * @property $zot_runningTime__objs
 * @property $zot_runningTime__obj
 * @property $zot_runningTime__terms
 * @property $zot_runningTime__term
 * @property $zot_runningTime__array
 *
 * @property $zot_history
 * @property $zot_history__html
 * @property $zot_history__float
 * @property $zot_history__int
 * @property $zot_history__id
 * @property $zot_history__ids
 * @property $zot_history__objs
 * @property $zot_history__obj
 * @property $zot_history__terms
 * @property $zot_history__term
 * @property $zot_history__array
 *
 * @property $zot_seriesText
 * @property $zot_seriesText__html
 * @property $zot_seriesText__float
 * @property $zot_seriesText__int
 * @property $zot_seriesText__id
 * @property $zot_seriesText__ids
 * @property $zot_seriesText__objs
 * @property $zot_seriesText__obj
 * @property $zot_seriesText__terms
 * @property $zot_seriesText__term
 * @property $zot_seriesText__array
 *
 * @property $zot_priorityNumbers
 * @property $zot_priorityNumbers__html
 * @property $zot_priorityNumbers__float
 * @property $zot_priorityNumbers__int
 * @property $zot_priorityNumbers__id
 * @property $zot_priorityNumbers__ids
 * @property $zot_priorityNumbers__objs
 * @property $zot_priorityNumbers__obj
 * @property $zot_priorityNumbers__terms
 * @property $zot_priorityNumbers__term
 * @property $zot_priorityNumbers__array
 *
 * @property $zot_ISSN
 * @property $zot_ISSN__html
 * @property $zot_ISSN__float
 * @property $zot_ISSN__int
 * @property $zot_ISSN__id
 * @property $zot_ISSN__ids
 * @property $zot_ISSN__objs
 * @property $zot_ISSN__obj
 * @property $zot_ISSN__terms
 * @property $zot_ISSN__term
 * @property $zot_ISSN__array
 *
 * @property $zot_journalAbbreviation
 * @property $zot_journalAbbreviation__html
 * @property $zot_journalAbbreviation__float
 * @property $zot_journalAbbreviation__int
 * @property $zot_journalAbbreviation__id
 * @property $zot_journalAbbreviation__ids
 * @property $zot_journalAbbreviation__objs
 * @property $zot_journalAbbreviation__obj
 * @property $zot_journalAbbreviation__terms
 * @property $zot_journalAbbreviation__term
 * @property $zot_journalAbbreviation__array
 *
 * @property $zot_issueDate
 * @property $zot_issueDate__html
 * @property $zot_issueDate__float
 * @property $zot_issueDate__int
 * @property $zot_issueDate__id
 * @property $zot_issueDate__ids
 * @property $zot_issueDate__objs
 * @property $zot_issueDate__obj
 * @property $zot_issueDate__terms
 * @property $zot_issueDate__term
 * @property $zot_issueDate__array
 *
 * @property $zot_ISBN
 * @property $zot_ISBN__html
 * @property $zot_ISBN__float
 * @property $zot_ISBN__int
 * @property $zot_ISBN__id
 * @property $zot_ISBN__ids
 * @property $zot_ISBN__objs
 * @property $zot_ISBN__obj
 * @property $zot_ISBN__terms
 * @property $zot_ISBN__term
 * @property $zot_ISBN__array
 *
 * @property $zot_references
 * @property $zot_references__html
 * @property $zot_references__float
 * @property $zot_references__int
 * @property $zot_references__id
 * @property $zot_references__ids
 * @property $zot_references__objs
 * @property $zot_references__obj
 * @property $zot_references__terms
 * @property $zot_references__term
 * @property $zot_references__array
 *
 * @property $zot_DOI
 * @property $zot_DOI__html
 * @property $zot_DOI__float
 * @property $zot_DOI__int
 * @property $zot_DOI__id
 * @property $zot_DOI__ids
 * @property $zot_DOI__objs
 * @property $zot_DOI__obj
 * @property $zot_DOI__terms
 * @property $zot_DOI__term
 * @property $zot_DOI__array
 *
 * @property $zot_legalStatus
 * @property $zot_legalStatus__html
 * @property $zot_legalStatus__float
 * @property $zot_legalStatus__int
 * @property $zot_legalStatus__id
 * @property $zot_legalStatus__ids
 * @property $zot_legalStatus__objs
 * @property $zot_legalStatus__obj
 * @property $zot_legalStatus__terms
 * @property $zot_legalStatus__term
 * @property $zot_legalStatus__array
 *
 * @property $zot_archive
 * @property $zot_archive__html
 * @property $zot_archive__float
 * @property $zot_archive__int
 * @property $zot_archive__id
 * @property $zot_archive__ids
 * @property $zot_archive__objs
 * @property $zot_archive__obj
 * @property $zot_archive__terms
 * @property $zot_archive__term
 * @property $zot_archive__array
 *
 * @property $zot_archiveLocation
 * @property $zot_archiveLocation__html
 * @property $zot_archiveLocation__float
 * @property $zot_archiveLocation__int
 * @property $zot_archiveLocation__id
 * @property $zot_archiveLocation__ids
 * @property $zot_archiveLocation__objs
 * @property $zot_archiveLocation__obj
 * @property $zot_archiveLocation__terms
 * @property $zot_archiveLocation__term
 * @property $zot_archiveLocation__array
 *
 * @property $zot_callNumber
 * @property $zot_callNumber__html
 * @property $zot_callNumber__float
 * @property $zot_callNumber__int
 * @property $zot_callNumber__id
 * @property $zot_callNumber__ids
 * @property $zot_callNumber__objs
 * @property $zot_callNumber__obj
 * @property $zot_callNumber__terms
 * @property $zot_callNumber__term
 * @property $zot_callNumber__array
 *
 * @property $zot_libraryCatalog
 * @property $zot_libraryCatalog__html
 * @property $zot_libraryCatalog__float
 * @property $zot_libraryCatalog__int
 * @property $zot_libraryCatalog__id
 * @property $zot_libraryCatalog__ids
 * @property $zot_libraryCatalog__objs
 * @property $zot_libraryCatalog__obj
 * @property $zot_libraryCatalog__terms
 * @property $zot_libraryCatalog__term
 * @property $zot_libraryCatalog__array
 *
 * @property $zot_note
 * @property $zot_note__html
 * @property $zot_note__float
 * @property $zot_note__int
 * @property $zot_note__id
 * @property $zot_note__ids
 * @property $zot_note__objs
 * @property $zot_note__obj
 * @property $zot_note__terms
 * @property $zot_note__term
 * @property $zot_note__array
 *
 * @property $zot_import
 * @property $zot_import__html
 * @property $zot_import__float
 * @property $zot_import__int
 * @property $zot_import__id
 * @property $zot_import__ids
 * @property $zot_import__objs
 * @property $zot_import__obj
 * @property $zot_import__terms
 * @property $zot_import__term
 * @property $zot_import__array
 *
 * @property $zot_itemKey
 * @property $zot_itemKey__html
 * @property $zot_itemKey__float
 * @property $zot_itemKey__int
 * @property $zot_itemKey__id
 * @property $zot_itemKey__ids
 * @property $zot_itemKey__objs
 * @property $zot_itemKey__obj
 * @property $zot_itemKey__terms
 * @property $zot_itemKey__term
 * @property $zot_itemKey__array
 *
 * @property $zot_itemVersion
 * @property $zot_itemVersion__html
 * @property $zot_itemVersion__float
 * @property $zot_itemVersion__int
 * @property $zot_itemVersion__id
 * @property $zot_itemVersion__ids
 * @property $zot_itemVersion__objs
 * @property $zot_itemVersion__obj
 * @property $zot_itemVersion__terms
 * @property $zot_itemVersion__term
 * @property $zot_itemVersion__array
 *
 * @property $zot_libraryId
 * @property $zot_libraryId__html
 * @property $zot_libraryId__float
 * @property $zot_libraryId__int
 * @property $zot_libraryId__id
 * @property $zot_libraryId__ids
 * @property $zot_libraryId__objs
 * @property $zot_libraryId__obj
 * @property $zot_libraryId__terms
 * @property $zot_libraryId__term
 * @property $zot_libraryId__array
 *
 * @property $zot_parentItem
 * @property $zot_parentItem__html
 * @property $zot_parentItem__float
 * @property $zot_parentItem__int
 * @property $zot_parentItem__id
 * @property $zot_parentItem__ids
 * @property $zot_parentItem__objs
 * @property $zot_parentItem__obj
 * @property $zot_parentItem__terms
 * @property $zot_parentItem__term
 * @property $zot_parentItem__array
 *
 * @property $zot_dateAdded
 * @property $zot_dateAdded__html
 * @property $zot_dateAdded__float
 * @property $zot_dateAdded__int
 * @property $zot_dateAdded__id
 * @property $zot_dateAdded__ids
 * @property $zot_dateAdded__objs
 * @property $zot_dateAdded__obj
 * @property $zot_dateAdded__terms
 * @property $zot_dateAdded__term
 * @property $zot_dateAdded__array
 *
 * @property $zot_dateModified
 * @property $zot_dateModified__html
 * @property $zot_dateModified__float
 * @property $zot_dateModified__int
 * @property $zot_dateModified__id
 * @property $zot_dateModified__ids
 * @property $zot_dateModified__objs
 * @property $zot_dateModified__obj
 * @property $zot_dateModified__terms
 * @property $zot_dateModified__term
 * @property $zot_dateModified__array
 *
 * @property $zot_tags
 * @property $zot_tags__html
 * @property $zot_tags__float
 * @property $zot_tags__int
 * @property $zot_tags__id
 * @property $zot_tags__ids
 * @property $zot_tags__objs
 * @property $zot_tags__obj
 * @property $zot_tags__terms
 * @property $zot_tags__term
 * @property $zot_tags__array
 *
 * @property $zot_unmatched_tags
 * @property $zot_unmatched_tags__html
 * @property $zot_unmatched_tags__float
 * @property $zot_unmatched_tags__int
 * @property $zot_unmatched_tags__id
 * @property $zot_unmatched_tags__ids
 * @property $zot_unmatched_tags__objs
 * @property $zot_unmatched_tags__obj
 * @property $zot_unmatched_tags__terms
 * @property $zot_unmatched_tags__term
 * @property $zot_unmatched_tags__array
 *
 * @property $zot_collections
 * @property $zot_collections__html
 * @property $zot_collections__float
 * @property $zot_collections__int
 * @property $zot_collections__id
 * @property $zot_collections__ids
 * @property $zot_collections__objs
 * @property $zot_collections__obj
 * @property $zot_collections__terms
 * @property $zot_collections__term
 * @property $zot_collections__array
 *
 * @property $zot_style
 * @property $zot_style__html
 * @property $zot_style__float
 * @property $zot_style__int
 * @property $zot_style__id
 * @property $zot_style__ids
 * @property $zot_style__objs
 * @property $zot_style__obj
 * @property $zot_style__terms
 * @property $zot_style__term
 * @property $zot_style__array
 *
 * @property $zot_bibliography
 * @property $zot_bibliography__html
 * @property $zot_bibliography__float
 * @property $zot_bibliography__int
 * @property $zot_bibliography__id
 * @property $zot_bibliography__ids
 * @property $zot_bibliography__objs
 * @property $zot_bibliography__obj
 * @property $zot_bibliography__terms
 * @property $zot_bibliography__term
 * @property $zot_bibliography__array
 *
 * @property $zot_citation
 * @property $zot_citation__html
 * @property $zot_citation__float
 * @property $zot_citation__int
 * @property $zot_citation__id
 * @property $zot_citation__ids
 * @property $zot_citation__objs
 * @property $zot_citation__obj
 * @property $zot_citation__terms
 * @property $zot_citation__term
 * @property $zot_citation__array
 */
trait dtm_zotero_trait
{

//    public static function registerTransforms($schema)
//    {
//    }


    /**
     * @param Oes_DTM_Schema $schema
     */
    public static function registerTransforms($schema)
    {

        parent::registerTransforms($schema);

        $schema->addTransformFunction(null, [
            Oes_Zotero::attr_zot_bibliography,
        ], function ($postid) {
            self::updateCitationTxt($postid);
        });

//        $schema->addTransformFunction(null, [
//            Oes_Zotero::attr_zot_title,
//            Oes_Zotero::attr_zot_bookTitle,
//        ], function ($postid) {
//
//            /**
//             * @var dtm_zotero_trait $dtm
//             */
//            $dtm = oes_dtm_form::init($postid);
//
//            $dtm->update_x_sort_title();
//
//        });

        $schema->addTransformFunction(null, [
            'zot_bookTitle', 'zot_publicationTitle',
            'zot_websiteTitle', 'zot_publisher'
        ], function ($postid) {

            /**
             * @var dtm_zotero_trait $dtm
             */
            $dtm = oes_dtm_form::init($postid);

            $dtm->update_u_publication_title();

        });

        $schema->addTransformFunction(null, [
            'zot_creators',
        ], function ($postid) {

            /**
             * @var dtm_zotero_trait $dtm
             */
            $dtm = oes_dtm_form::init($postid);

            $dtm->update_creators_related();

        });

    }

    static function updateCitationTxt($postid)
    {
        /**
         * @var dtm_zotero_trait $bib
         */
        $bib = oes_dtm_form::init($postid);
        $bib->citation_text = trim(strip_tags($bib->zot_bibliography));
        return $bib;
    }

    function update_creators_related()
    {

        $creators = $this->zot_creators__array;

        $author_names = array_map(function ($o) {
            return ['name' => Oes_Zotero::buildCreatorName($o)];
        }, x_filter_array_by_property($creators, 'creatorType', 'author'));

        $author_types = array_map(function ($o) {
            return $o['creatorType'];
        }, x_filter_array_by_property($creators, 'creatorType', 'author'));

        $not_author_names = array_map(function ($o) {
            return ['name' => Oes_Zotero::buildCreatorName($o)];
        }, x_filter_array_by_property($creators, 'creatorType', 'author', true));

        $not_author_types = array_map(function ($o) {
            return $o['creatorType'];
        }, x_filter_array_by_property($creators, 'creatorType', 'author', true));

        $contributor_names = array_map(function ($o) {
            return ['name' => Oes_Zotero::buildCreatorName($o)];
        }, $creators);

//        $contributor_types = array_map(function ($o) {
//            return self::buildCreatorName($o);
//        }, x_filter_array_by_property($creators, 'creatorType', 'author', true));
//
//        $contributor_ids = array_map(function ($o) {
//            return self::buildCreatorName($o);
//        }, x_filter_array_by_property($creators, 'creatorType', 'author', true));

        $this->u_author_names = $author_names;
        $this->u_non_author_names = $not_author_names;
        $this->u_creator_names = $contributor_names;

//        $vals['author_types_ss'] = $author_types;
//        $vals['not_author_types_ss'] = $not_author_types;
//

    }

    function update_u_publication_title()
    {
        if ($this->zot_bookTitle) {
            $this->u_publication_title = $this->zot_bookTitle;
        } else if ($this->zot_publicationTitle) {
            $this->u_publication_title = $this->zot_publicationTitle;
        } else if ($this->zot_websiteTitle) {
            $this->u_publication_title = $this->zot_websiteTitle;
        } else if ($this->zot_publisher) {
            $this->u_publication_title = $this->zot_publisher;
        }


    }

    function update_x_sort_title()
    {

        $x_title_sort = normalizeToSimpleSortAsciiWithGreek($this->zot_title);

        $x_title_sort_class = mb_substr($x_title_sort, 0, 1);
        if (!preg_match('@[a-zA-Z\p{Greek}]@u', $x_title_sort_class)) {
            $x_title_sort_class = '#';
        }

        $this->x_title_sort = $x_title_sort;
        $this->x_title_sort_class = $x_title_sort_class;
        $this->x_title_list = $this->zot_title;

    }

    static function extractExtraKeyValues($postid)
    {
        /**
         * @var dtm_zotero_trait $dtm
         */
        $dtm = oes_dtm_form::init($postid);
        $extra = $dtm->zot_extra;
        if (empty($extra)) {
            return $dtm;
        }
        $parts = explode("\n",$extra);
        $values = [];
        foreach ($parts as $part)
        {
            if (startswith($part, 'Citation Key: ')) {
                $dtm->citation_key = str_replace('Citation Key: ','', $part);
            } else if (preg_match('@\{:(.*?): (.*?)\}@',$part,$matches)) {
                $values[] = ['key'=>$matches[1], 'value' => trim($matches[2])];
            }
        }
        $dtm->zot_extraKeyValues = $values;
        return $dtm;
    }



}