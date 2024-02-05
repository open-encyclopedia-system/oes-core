import {useBlockProps} from '@wordpress/block-editor';

export default function Edit() {
    return (
        <div {...useBlockProps()}>
            <div className="oes-index-archive-filter-wrapper">
                <ul className="oes-vertical-list">
                    <li><a className="oes-index-archive-filter-all oes-index-filter-anchor">All</a></li>
                    <li><a className="oes-index-filter-anchor">Persons</a></li>
                    <li><a className="oes-index-filter-anchor">Places</a></li>
                    <li><a className="oes-index-filter-anchor">Events</a></li>
                    <li className="active"><a className="oes-index-filter-anchor active">Institutions</a></li>
                    <li><a className="oes-index-filter-anchor">Keywords</a>
                    </li>
                </ul>
            </div>
        </div>
    );
}
