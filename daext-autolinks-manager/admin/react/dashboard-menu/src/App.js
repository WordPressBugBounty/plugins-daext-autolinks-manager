import Table from './components/Table';
import {filterSelectStyles} from '../../utils/utils';
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";
import Select from 'react-select';

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;
const useRef = wp.element.useRef;

const {__} = wp.i18n;

/**
 * Filterable columns available per dashboard tab.
 * Note: 'num_il_clicks' and 'auto_links_visits' are pro-only — omitted here.
 */
const FILTERABLE_COLUMNS = {
    internal: [
        { value: 'post_title',        label: __( 'Post Title',             'daext-autolinks-manager' ), type: 'text'    },
        { value: 'post_date',         label: __( 'Date',                   'daext-autolinks-manager' ), type: 'text'    },
        { value: 'post_type',         label: __( 'Post Type',              'daext-autolinks-manager' ), type: 'text'    },
        { value: 'content_length',    label: __( 'Length',                 'daext-autolinks-manager' ), type: 'numeric' },
        { value: 'manual_interlinks', label: __( 'Manual Internal Links',  'daext-autolinks-manager' ), type: 'numeric' },
        { value: 'auto_interlinks',   label: __( 'Auto Internal Links',    'daext-autolinks-manager' ), type: 'numeric' },
        { value: 'iil',               label: __( 'Inbound Internal Links', 'daext-autolinks-manager' ), type: 'numeric' },
        { value: 'optimization',      label: __( 'Status (0 / 1)',         'daext-autolinks-manager' ), type: 'numeric' },
    ],
    automatic: [
        { value: 'post_title',     label: __( 'Post Title',      'daext-autolinks-manager' ), type: 'text'    },
        { value: 'post_date',      label: __( 'Date',            'daext-autolinks-manager' ), type: 'text'    },
        { value: 'post_type',      label: __( 'Post Type',       'daext-autolinks-manager' ), type: 'text'    },
        { value: 'content_length', label: __( 'Length',          'daext-autolinks-manager' ), type: 'numeric' },
        { value: 'auto_links',     label: __( 'Automatic Links', 'daext-autolinks-manager' ), type: 'numeric' },
    ],
};

let statisticsAlDataLastUpdate = window.DAEXTAM_PARAMETERS.statistics_al_data_last_update;
let statisticsIlDataLastUpdate = window.DAEXTAM_PARAMETERS.statistics_il_data_last_update;
let statisticsDataUpdateFrequency = window.DAEXTAM_PARAMETERS.statistics_data_update_frequency;
let currentTime = window.DAEXTAM_PARAMETERS.current_time;

/**
 * Determines whether an automatic data update is needed for the given view.
 */
function isAutomaticUpdateNeeded( activeView ) {
    let dataLastUpdate;
    let dataUpdateFrequency;

    if ( activeView === 'automatic' ) {
        dataLastUpdate      = statisticsAlDataLastUpdate;
        dataUpdateFrequency = statisticsDataUpdateFrequency;
    } else if ( activeView === 'internal' ) {
        dataLastUpdate      = statisticsIlDataLastUpdate;
        dataUpdateFrequency = 'hourly';
    } else {
        return false;
    }

    if ( dataLastUpdate === '' || dataLastUpdate === false || dataLastUpdate === undefined ) {
        return true;
    }

    let date = new Date( currentTime );

    switch ( dataUpdateFrequency ) {
        case 'hourly':
            date.setHours( date.getHours() - 1 );
            return new Date( dataLastUpdate ) < date;
        case 'daily':
            date.setDate( date.getDate() - 1 );
            return new Date( dataLastUpdate ) < date;
        case 'weekly':
            date.setDate( date.getDate() - 7 );
            return new Date( dataLastUpdate ) < date;
        case 'monthly':
            date.setMonth( date.getMonth() - 1 );
            return new Date( dataLastUpdate ) < date;
    }

    return false;
}

const App = () => {

    const [formData, setFormData] = useState({
        searchString: '',
        searchStringChanged: false,
        sortingColumn: 'post_date',
        sortingOrder: 'desc',
        filterColumn: 'post_title',
    });

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataAlRefreshStatistics, setDataAlRefreshStatistics] = useState(false);
    const [dataIlRefreshStatistics, setDataIlRefreshStatistics] = useState(false);

    const skipNextFetch = useRef(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allPosts: 0,
        averageAl: 0,
        averageMil: 0,
        averageAil: 0,
        averageInboundLinks: 0,
    });

    const [activeView, setActiveView] = useState('internal');

    const Tabs = ({activeView, setActiveView}) => (
        <div className="daextam-tabs">
            <button
                className="daextam-tab"
                data-active={activeView === 'internal'}
                onClick={() => setActiveView('internal')}
            >
                {__('Internal Links', 'daext-autolinks-manager')}
            </button>
            <button
                className="daextam-tab"
                data-active={activeView === 'automatic'}
                onClick={() => setActiveView('automatic')}
            >
                {__('Automatic Links', 'daext-autolinks-manager')}
            </button>
        </div>
    );

    useEffect(() => {

        const autoUpdateAl = isAutomaticUpdateNeeded('automatic');
        const autoUpdateIl = isAutomaticUpdateNeeded('internal');

        if ( 'automatic' === activeView && autoUpdateAl && !dataAlRefreshStatistics ) {
            statisticsAlDataLastUpdate = currentTime;
            setDataAlRefreshStatistics(true);
            return;
        }

        if ( 'internal' === activeView && autoUpdateIl && !dataIlRefreshStatistics ) {
            statisticsIlDataLastUpdate = currentTime;
            setDataIlRefreshStatistics(true);
            return;
        }

        if (skipNextFetch.current) {
            skipNextFetch.current = false;
            return;
        }

        setDataAreLoading(true);

        const getRefreshStatistics = () => {
            if ('automatic' === activeView) return dataAlRefreshStatistics;
            if ('internal'  === activeView) return dataIlRefreshStatistics;
            return false;
        };

        wp.apiFetch({
            path: '/daext-autolinks-manager/v1/statistics',
            method: 'POST',
            data: {
                search_string:      formData.searchString,
                sorting_column:     formData.sortingColumn,
                sorting_order:      formData.sortingOrder,
                refresh_statistics: getRefreshStatistics(),
                active_view:        activeView,
                filter_column:      formData.filterColumn,
            }
        }).then(data => {

            setTableData(data.table);

            if ('automatic' === activeView) {
                setStatistics({
                    allPosts:  data.statistics.all_posts,
                    averageAl: data.statistics.average_al,
                });
            } else if ('internal' === activeView) {
                setStatistics({
                    allPosts:             data.statistics.all_posts,
                    averageMil:           data.statistics.average_mil,
                    averageAil:           data.statistics.average_ail,
                    averageInboundLinks:  data.statistics.average_inbound_links,
                });
            }

            if (dataAlRefreshStatistics || dataIlRefreshStatistics) {
                skipNextFetch.current = true;
                setDataAlRefreshStatistics(false);
                setDataIlRefreshStatistics(false);
                setFormData({
                    searchString:        '',
                    searchStringChanged: false,
                    sortingColumn:       'post_date',
                    sortingOrder:        'desc',
                    filterColumn:        ( FILTERABLE_COLUMNS[activeView] || [] )[0]?.value || 'post_title',
                });
            }

            setDataAreLoading(false);
        });

    }, [
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder,
        dataAlRefreshStatistics,
        dataIlRefreshStatistics,
        activeView,
    ]);

    // Reset filter/search when switching tabs.
    useEffect(() => {
        const defaultColumn = ( FILTERABLE_COLUMNS[activeView] || [] )[0]?.value || 'post_title';
        setFormData(prev => ({
            ...prev,
            searchString:  '',
            filterColumn:  defaultColumn,
            sortingColumn: 'post_date',
            sortingOrder:  'desc',
        }));
    }, [activeView]);

    function handleKeyUp(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            document.getElementById('daextam-search-button').click();
        }
    }

    function getFilterPlaceholder(view, filterColumn) {
        const columns = FILTERABLE_COLUMNS[view] || [];
        const col     = columns.find(c => c.value === filterColumn);
        if (!col) {
            return __('Filter…', 'daext-autolinks-manager');
        }
        if (col.type === 'numeric') {
            return __('e.g. 0, >5, <=10', 'daext-autolinks-manager');
        }
        return __('Filter by', 'daext-autolinks-manager') + ' ' + col.label;
    }

    function handleSortingChanges(e) {
        let sortingOrder = formData.sortingOrder;
        if (formData.sortingColumn === e.target.value) {
            sortingOrder = formData.sortingOrder === 'asc' ? 'desc' : 'asc';
        }
        setFormData({
            ...formData,
            sortingColumn: e.target.value,
            sortingOrder:  sortingOrder,
        });
    }

    function handleDataAlRefreshStatistics() {
        setDataAlRefreshStatistics(prev => !prev);
    }

    function handleDataIlRefreshStatistics() {
        setDataIlRefreshStatistics(prev => !prev);
    }

    const handleRefresh = 'automatic' === activeView ? handleDataAlRefreshStatistics : handleDataIlRefreshStatistics;

    return (
        <>
            <React.StrictMode>

                <Tabs activeView={activeView} setActiveView={setActiveView} />

                {
                    !dataAreLoading ? (

                        <div className={'daextam-react-table'} data-active-view={activeView}>

                            <div className={'daextam-react-table-header'}>
                                <div className={'statistics'}>

                                    <div className={'statistic-label'}>{__('All posts', 'daext-autolinks-manager')}:</div>
                                    <div className={'statistic-value'}>{statistics.allPosts}</div>

                                    {activeView === 'automatic' && <>
                                        <div className={'statistic-label'}>{__('Average Automatic Links', 'daext-autolinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.averageAl}</div>
                                    </>}

                                    {activeView === 'internal' && <>
                                        <div title={__('The average number of manual internal links per post.', 'daext-autolinks-manager')} className={'statistic-label'}>{__('Avg. Manual Internal Links', 'daext-autolinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.averageMil}</div>
                                        <div title={__('The average number of automatic internal links per post.', 'daext-autolinks-manager')} className={'statistic-label'}>{__('Avg. Auto Internal Links', 'daext-autolinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.averageAil}</div>
                                        <div title={__('The average number of inbound internal links.', 'daext-autolinks-manager')} className={'statistic-label'}>{__('Avg. Inbound Links', 'daext-autolinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.averageInboundLinks}</div>
                                    </>}

                                </div>
                                <div className={'tools-actions'}>
                                    <button onClick={handleRefresh}>
                                        <img src={RefreshIcon} className={'button-icon'} />
                                        {__('Update metrics', 'daext-autolinks-manager')}
                                    </button>
                                </div>
                            </div>

                            <div className={'daextam-react-table__daextam-filters daextam-react-table__daextam-filters-dashboard-menu'}>
                                <div className={'daextam-search-container'}>
                                    <Select
                                        classNamePrefix={'daextam-filter-select'}
                                        isSearchable={false}
                                        aria-label={__('Filter column', 'daext-autolinks-manager')}
                                        value={( FILTERABLE_COLUMNS[activeView] || [] ).find(col => col.value === formData.filterColumn) || null}
                                        onChange={(selectedOption) => setFormData({
                                            ...formData,
                                            filterColumn: selectedOption.value,
                                            searchString: '',
                                        })}
                                        options={( FILTERABLE_COLUMNS[activeView] || [] )}
                                        styles={filterSelectStyles}
                                    />
                                    <input
                                        onKeyUp={handleKeyUp}
                                        type={'text'}
                                        placeholder={getFilterPlaceholder(activeView, formData.filterColumn)}
                                        value={formData.searchString}
                                        onChange={(event) => setFormData({
                                            ...formData,
                                            searchString: event.target.value,
                                        })}
                                    />
                                    <input
                                        id={'daextam-search-button'}
                                        className={'daextam-btn daextam-btn-secondary'}
                                        type={'submit'}
                                        value={__('Search', 'daext-autolinks-manager')}
                                        onClick={() => setFormData({
                                            ...formData,
                                            searchStringChanged: !formData.searchStringChanged,
                                        })}
                                    />
                                </div>
                            </div>

                            <Table
                                data={tableData}
                                handleSortingChanges={handleSortingChanges}
                                formData={formData}
                                view={activeView}
                            />

                        </div>

                    ) : (
                        <LoadingScreen
                            loadingDataMessage={__('Loading data...', 'daext-autolinks-manager')}
                            generatingDataMessage={__('Data is being generated. For large sites, this process may take several minutes. Please wait...', 'daext-autolinks-manager')}
                            dataUpdateRequired={dataAlRefreshStatistics || dataIlRefreshStatistics}
                        />
                    )
                }

            </React.StrictMode>
        </>
    );

};
export default App;
