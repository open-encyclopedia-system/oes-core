import {useBlockProps} from '@wordpress/block-editor';

export default function Edit({attributes}) {

    let {className} = attributes;
    if (className === undefined) className = 'is-style-oes-default';

    let switchLabel = '';
    let languageArray = [];
    if (oesLanguageArray == null) oesLanguageArray = [];
    for (const [, value] of Object.entries(oesLanguageArray)) {
        if (value.hasOwnProperty('abb')) languageArray.push(value.abb);
    }

    /* Set defaults if no language is found */
    if (languageArray.length < 1) languageArray.push('LANG');

    if (className === 'is-style-oes-default') switchLabel = languageArray.join(' | ');
    else if (className === 'is-style-oes-popup' || className === 'is-style-oes-two')
        switchLabel = Object.values(languageArray)[0];

    return (<div {...useBlockProps()}>{switchLabel}</div>);
}