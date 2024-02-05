import {useBlockProps} from '@wordpress/block-editor';

export default function Edit() {
    return (
        <div {...useBlockProps()}>
            <ul className="oes-filter-list-container oes-vertical-list">
                <li>
                    <details className="wp-block-details active" open>
                        <summary>Lorem ipsum</summary>
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
                <li>
                    <details className="wp-block-details">
                        <summary>Aliquyam</summary>
                    </details>
                    <ul className="oes-filter-list oes-vertical-list"></ul>
                </li>
                <li>
                    <details className="wp-block-details" open>
                        <summary>Voluptua</summary>
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
        </div>
    );
}
