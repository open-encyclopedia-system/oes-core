import {useBlockProps} from '@wordpress/block-editor';

function DetailsFilter() {
    return (
        <ul className="oes-filter-list-container oes-vertical-list">
            <li className="oes-filter-list-type-default">
                <details className="wp-block-details active" open>
                    <summary><span className="oes-filter-component">Lorem ipsum</span></summary>
                </details>
                <ul className="oes-filter-list oes-vertical-list">
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>Dolor</span>
                            <span className="oes-filter-item-count">(+)</span>
                        </a>
                    </li>
                    <li className="oes-archive-filter-item active">
                        <a className="oes-archive-filter">
                            <span>Sadipscing</span>
                            <span className="oes-filter-item-count">(-)</span>
                        </a>
                    </li>
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>Sit consetetur</span>
                            <span className="oes-filter-item-count">(+)</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li className="oes-filter-list-type-default">
                <details className="wp-block-details">
                    <summary><span className="oes-filter-component">Aliquyam</span></summary>
                </details>
                <ul className="oes-filter-list oes-vertical-list"/>
            </li>
            <li className="oes-filter-list-type-default">
                <details className="wp-block-details" open>
                    <summary><span className="oes-filter-component">Voluptua</span></summary>
                </details>
                <ul className="oes-filter-list oes-vertical-list">
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>Clita Kasd</span>
                            <span className="oes-filter-item-count">(3)</span>
                        </a>
                    </li>
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>Takimata</span>
                            <span className="oes-filter-item-count">(7)</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    );
}

function DatalistFilter() {
    return (
        <ul className="oes-filter-list-container oes-vertical-list">
            {['Lorem ipsum', 'Aliquyam', 'Voluptua'].map((label) => (
                <li key={label} className="oes-filter-list-type-datalist">
                    <label>{label}</label>
                    <input type="text" placeholder="Search..."/>
                </li>
            ))}
        </ul>
    );
}

function ClassicFilter() {
    return (
        <ul className="oes-filter-list-container oes-vertical-list">
            <li className="oes-filter-list-type-classic">
                <span className="oes-filter-component">Lorem ipsum</span>
                <ul className="oes-filter-list oes-vertical-list">
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>Dolor</span>
                            <span className="oes-filter-item-count">(+)</span>
                        </a>
                    </li>
                    <li className="oes-archive-filter-item active">
                        <a className="oes-archive-filter">
                            <span>Sadipscing</span>
                            <span className="oes-filter-item-count">(-)</span>
                        </a>
                    </li>
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>Sit consetetur</span>
                            <span className="oes-filter-item-count">(+)</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li className="oes-filter-list-type-classic">
                <span className="oes-filter-component">Aliquyam</span>
                <ul className="oes-filter-list oes-vertical-list">
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>gubergren</span>
                            <span className="oes-filter-item-count">(+)</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li className="oes-filter-list-type-classic">
                <span className="oes-filter-component">Voluptua</span>
                <ul className="oes-filter-list oes-vertical-list">
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>Clita Kasd</span>
                            <span className="oes-filter-item-count">(3)</span>
                        </a>
                    </li>
                    <li className="oes-archive-filter-item">
                        <a className="oes-archive-filter">
                            <span>Takimata</span>
                            <span className="oes-filter-item-count">(7)</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    );
}

const FILTER_COMPONENTS = {
    'is-style-oes-default': DetailsFilter,
    'is-style-oes-datalist': DatalistFilter,
};

export default function Edit({attributes}) {
    const {className = 'is-style-oes-default'} = attributes;
    const FilterComponent = FILTER_COMPONENTS[className] ?? ClassicFilter;

    return (
        <div {...useBlockProps()}>
            <FilterComponent/>
        </div>
    );
}