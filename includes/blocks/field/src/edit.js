import {__, sprintf} from '@wordpress/i18n';
import {useBlockProps} from '@wordpress/block-editor';
import {
    SelectControl,
    Spinner
} from '@wordpress/components';
import {getLanguageControls, getDisplayValueFromArray} from '../../blocks';
import apiFetch from '@wordpress/api-fetch';
import {useEffect, useState} from '@wordpress/element';

export default function Edit({attributes, setAttributes, isSelected}) {

    const {
        field,
        header,
        prefix,
        relation
    } = attributes;


    const [fields, setFields] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {

        apiFetch({
            path: '/oes/v1/fields'
        })
            .then((result) => {

                setFields(result || []);
                setIsLoading(false);

            })
            .catch(() => {

                setFields([]);
                setIsLoading(false);

            });

    }, []);

    const fieldOptions = [
        {
            label: __('Select a field', 'oes'),
            value: ''
        },
        ...fields.map((singleField) => ({
            label: singleField.label || singleField.name,
            value: singleField.name
        }))
    ];

    const selectedField = fields.find(
        (f) => f.name === field
    );

    const selectedLabel = selectedField?.label || field;

    const selectedType = selectedField?.type || 'text';

    const showRelationControl =
        selectedType === 'relationship' ||
        selectedType === 'post_object';

    if (isSelected) {
        return (
            <div {...useBlockProps()}>
                <div className="components-placeholder components-placeholder is-large">
                    <div className="components-placeholder__label">{__('Field', 'oes')}</div>

                    {isLoading ? (
                        <Spinner/>
                    ) : (

                        <SelectControl
                            label={__('Select a Field', 'oes')}
                            value={field}
                            options={fieldOptions}
                            onChange={(value) =>
                                setAttributes({
                                    field: value
                                })
                            }
                        />
                    )}

                    {showRelationControl && (
                        <SelectControl
                            label="Relation"
                            value={relation}
                            options={[
                                {
                                    label: 'None',
                                    value: ''
                                },
                                {
                                    label: 'Parent',
                                    value: 'parent'
                                },
                                {
                                    label: 'Version',
                                    value: 'version'
                                }
                            ]}
                            onChange={(value) =>
                                setAttributes({relation: value})
                            }
                        />
                    )}

                    <div className="oes-block-subheader">{__('Header', 'oes')}</div>

                    {getLanguageControls(header, setAttributes, 'header')}

                    <div className="oes-block-subheader">{__('Prefix', 'oes')}</div>

                    {getLanguageControls(prefix, setAttributes, 'prefix')}

                </div>
            </div>
        );
    } else {

        const headerText = getDisplayValueFromArray(header, '');
        const prefixText = getDisplayValueFromArray(prefix, '');

        return (
            <div {...useBlockProps()}>
                {headerText && (
                    <h2 className="oes-content-table-header">
                        {headerText}
                    </h2>
                )}

                {prefixText}

                {sprintf(
                    __('Field value of [%s]', 'oes'),
                    selectedLabel || __('No field selected', 'oes')
                )}
            </div>
        );
    }
}
