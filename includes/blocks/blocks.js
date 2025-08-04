import {__} from '@wordpress/i18n';
import {TextControl, SelectControl} from '@wordpress/components';

/**
 * Get language text controls.
 */
export function getLanguageControls(values = {}, setAttributes, attributeKey = 'labels') {
    if (typeof oesLanguageArray === 'undefined') oesLanguageArray = {};

    const textControls = [];
    for (const [langCode, langInfo] of Object.entries(oesLanguageArray)) {
        textControls.push(
            <TextControl
                key={langCode}
                label={langInfo.label}
                value={values[langCode] ?? ''}
                help={__('Add label for this language', 'oes') + ' (' + langCode + ')'}
                onChange={(val) => {
                    const newValues = {...values, [langCode]: val};
                    setAttributes({[attributeKey]: newValues});
                }}
            />
        );
    }

    return textControls;
}

/**
 * Get language select control.
 */
export function getLanguageSelect(currentValue, setAttributes, help) {
    if (!oesLanguageArray || Object.keys(oesLanguageArray).length < 1) return '';

    /* prepare default options for 'all' and 'displayed option'*/
    const options = [
        {value: 'all', label: __('All languages', 'oes')},
        {value: 'current', label: __('Currently displayed language', 'oes')},
    ];

    /* add options for opposite language if two language*/
    const oesLanguageArraySize = Object.keys(oesLanguageArray).length;
    if (oesLanguageArraySize === 2) {
        options.push({value: 'opposite', label: __('Opposite to currently displayed language', 'oes')});
    }

    /* add option if more than two languages */
    for (const [valueKey, value] of Object.entries(oesLanguageArray)) {
        options.push({value: valueKey, label: value.label});
    }

    return [
        <SelectControl
            label={__('Language', 'oes')}
            options={options}
            value={options[currentValue] !== undefined ? 'all' : currentValue}
            help={help}
            onChange={(val) => {
                setAttributes({language: String(val)});
            }}
        />
    ];
}

/**
 * Get post type options.
 */
export function getPostTypeOptions() {
    const postTypeOptions = [];
    const postTypes = wp.data.select('core').getPostTypes({per_page: -1});

    if (postTypes) {
        postTypeOptions.push({value: 0, label: '-'});
        postTypes.forEach((postType) => {
            if (postType.viewable) {
                postTypeOptions.push({value: postType.slug, label: postType.name});
            }
        });
    } else {
        postTypeOptions.push({value: 0, label: __('Loading...', 'oes')});
    }

    postTypeOptions.sort((a, b) => a.label.localeCompare(b.label));

    return postTypeOptions;
}

/**
 * Get display value from array.
 */
export function getDisplayValueFromArray(valueArray, defaultString) {
    if (valueArray == null) return defaultString;

    const displayValue = [];
    let defaultValue = '';

    for (const key in valueArray) {
        if (key === 'default') defaultValue = `[${valueArray[key]}]`;
        else displayValue.push(valueArray[key]);
    }

    if (defaultValue.length > 0) displayValue.push(defaultValue);

    return displayValue.length > 0 ? displayValue.join(' / ') : defaultString;
}
