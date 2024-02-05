import {__} from '@wordpress/i18n';
import {TextControl, SelectControl} from '@wordpress/components';


/**
 * Get the language text controls.
 */
export function getLanguageControls(values, setAttributes) {

    if(values === undefined) values = {};
    if (oesLanguageArray === undefined) oesLanguageArray = {};
    let textControls = [];
    for (const [valueKey, value] of Object.entries(oesLanguageArray)) {
        textControls.push(<TextControl
            label={value.label}
            value={(values[valueKey] !== undefined) ? values[valueKey] : ''}
            help={__("Add label for this language", "oes") + ' (' + valueKey + ').'}
            onChange={(val) => {
                let newLabels = { ...values};
                newLabels[valueKey] = val;
                setAttributes({
                    labels: newLabels
                })
            }}
        />);
    }
    return textControls;
}


/**
 * Get the language select.
 */
export function getLanguageSelect(currentValue, setAttributes, help) {

    if (oesLanguageArray == null || oesLanguageArray.length < 1) return '';

    let options = [];

    /* add all option */
    options.push({value:'all', label: __('All languages', 'oes')});

    /* add currently displayed option */
    options.push({value:'current', label: __('Currently displayed language', 'oes')});

    /* add opposite language option for two languages */
    const oesLanguageArraySize = Object.keys(oesLanguageArray).length;
    if (oesLanguageArraySize < 1) return '';
    if(oesLanguageArraySize === 2)
        options.push({value:'opposite', label: __('Opposite to currently displayed language', 'oes')});

    /* add language options */
    for (const [valueKey, value] of Object.entries(oesLanguageArray)) {
        options.push({value: valueKey, label: value.label})
    }

    let languageSelect = [];
    languageSelect.push(<SelectControl
        label={__('Language', 'oes')}
        options={options}
        value={(options[currentValue] !== undefined) ? 'all' : currentValue}
        help={help}
        onChange={(val) => {
            setAttributes({
                language: String(val)
            })
        }}
    />);
    return languageSelect;
}


/**
 * Get post type options.
 */
export function getPostTypeOptions(){

    /* prepare post type options */
    let postTypeOptions = [];
    const postTypes = wp.data.select('core').getPostTypes({per_page: -1});

    if (postTypes) {
        postTypeOptions.push({value: 0, label: '-'});
        postTypes.forEach((postType) => {
            if (postType.viewable) postTypeOptions.push({value: postType.slug, label: postType.name})
        })
    } else {
        postTypeOptions.push({value: 0, label: __('Loading...', 'oes')})
    }
    postTypeOptions.sort((a, b) => a.label.localeCompare(b.label));

    return postTypeOptions;
}


/**
 * Get display value from array.
 */
export function getDisplayValueFromArray(valueArray, defaultString) {
    let displayValue = [];
    if (valueArray == null) return defaultString;

    /* last: default */
    let defaultValue = '';
    for (const key in valueArray) {
        if (key === 'default') defaultValue = '[' + valueArray[key] + ']';
        else displayValue.push(valueArray[key]);
    }
    if (defaultValue.length > 0) displayValue.push(defaultValue);
    if (displayValue.length < 1) return defaultString;
    return displayValue.join(' / ');
}