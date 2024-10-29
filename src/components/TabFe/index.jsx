import { useTextContextProvider } from "../../Context/Text"

export default function TabFe({ currentTab, setCurrentTab }) {
    const textDataProvider = useTextContextProvider();
    return (<div className="fe-tablist">
        <ul>
            <li className={currentTab === 0 ? 'active' : 'inactive'}><a onClick={e => { e.preventDefault(); setCurrentTab(0)}} href="#">{textDataProvider.aruba_fe_connection_label}</a></li>
            <li className={currentTab === 1 ? 'active' : 'inactive'}><a onClick={e => { e.preventDefault(); setCurrentTab(1)}} href="#">{textDataProvider.aruba_fe_generic_label}</a></li>
            <li className={currentTab === 2 ? 'active' : 'inactive'}><a onClick={e => { e.preventDefault(); setCurrentTab(2)}} href="#">{textDataProvider.aruba_fe_payments_label}</a></li>
            <li className={currentTab === 3 ? 'active' : 'inactive'}><a onClick={e => { e.preventDefault(); setCurrentTab(3)}} href="#">{textDataProvider.aruba_fe_texes_label}</a></li>
        </ul>
    </div>)

}