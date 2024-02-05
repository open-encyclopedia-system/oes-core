import {useBlockProps} from '@wordpress/block-editor';

export default function Edit({attributes}) {

    let {className} = attributes;
    if (className === undefined) className = 'is-style-oes-default';

    return (
        <div {...useBlockProps()}>
            <ul className={className + " oes-alphabet-list oes-horizontal-list"}>
                <li><a className="oes-filter-abc">All</a></li>
                <li><a className="oes-filter-abc">A</a></li>
                <li><a className="oes-filter-abc">B</a></li>
                <li><span className="inactive">C</span></li>
                <li><a className="oes-filter-abc">D</a></li>
                <li><a className="oes-filter-abc">E</a></li>
                <li><a className="oes-filter-abc">F</a></li>
                <li><a className="oes-filter-abc">G</a></li>
                <li><span className="inactive">H</span></li>
                <li><span className="inactive">I</span></li>
                <li><span className="inactive">J</span></li>
                <li><span className="inactive">K</span></li>
                <li><a className="oes-filter-abc">L</a></li>
                <li><span className="inactive">M</span></li>
                <li><span className="inactive">N</span></li>
                <li><a className="oes-filter-abc">O</a></li>
                <li><span className="inactive">P</span></li>
                <li><span className="inactive">Q</span></li>
                <li><span className="inactive">R</span></li>
                <li><a className="oes-filter-abc">S</a></li>
                <li><span className="inactive">T</span></li>
                <li><a className="oes-filter-abc">U</a></li>
                <li><span className="inactive">V</span></li>
                <li><span className="inactive">W</span></li>
                <li><span className="inactive">X</span></li>
                <li><span className="inactive">Y</span></li>
                <li><span className="inactive">Z</span></li>
                <li><span className="inactive">#</span></li>
            </ul>
        </div>
    );
}
