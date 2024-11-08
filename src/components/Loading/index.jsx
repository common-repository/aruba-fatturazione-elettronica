//import ArubaFeLogo from '../../assets/images/loader.svg';
import React from 'react';
import { useTextContextProvider } from '../../Context/Text';

export default function () {

    const textDataProvider = useTextContextProvider();

    return (<div className="aruba-fe-loading">
        <div>
            <SvgLogo />
            <p className='loader'>{textDataProvider.aruba_fe_loading}</p>
        </div>
    </div>);

}

export function SvgLogo() {

    return (<svg version="1.1" id="Livello_1" xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
        viewBox="0 0 150 150" style={{ 'enableBackground': 'new 0 0 150 150' }} space="preserve">
        <path className="paperbackground" d="M118.5,14.5c-7.2,0-13.1,5.8-13.1,13.1c0,0.3,0.1,0.6,0.1,0.8l-0.1,0v9.4h26.1V27.5
   C131.6,20.3,125.7,14.5,118.5,14.5z"/>
        <path className="paperstroke" d="M132.4,29.7v24.6" />
        <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="43.1992" y1="10.3574" x2="106.7551" y2="120.4395">
            <stop offset="0%" stopColor="#009ADE">
                <animate attributeName="stopColor" values="#7A5FFF; #01FF89; #7A5FFF" dur="2s" repeatCount="indefinite"></animate>
            </stop>

            <stop offset="100%" stopColor="#1F71B8">
                <animate attributeName="stopColor" values="#01FF89; #7A5FFF; #01FF89" dur="2s" repeatCount="indefinite"></animate>
            </stop>

        </linearGradient>
        <path className="first-svg" d="M147.6,96.9H3.1V36.8c0-3.2,2.6-5.7,5.7-5.7h133c3.2,0,5.7,2.6,5.7,5.7V96.9z" />
        <g>
            <path className="base" d="M96.4,138.8H56.7c-0.3,0-0.6-0.3-0.6-0.6V136c0-0.3,0.2-0.6,0.6-0.6c6.9-0.5,9.7-2.3,9.9-12.6
       c0-0.3,0.3-0.6,0.6-0.6c0,0,0,0,0,0c0.3,0,0.6,0.3,0.6,0.6c-0.2,10.8-3.5,13.2-10.5,13.8v1.1h38.4v-1.1c-6-0.5-9.2-2.1-10.2-9.2
       c0-0.3,0.2-0.6,0.5-0.7c0.3-0.1,0.6,0.2,0.7,0.5c0.9,6.6,3.7,7.8,9.6,8.2c0.3,0,0.6,0.3,0.6,0.6v2.3
       C97,138.5,96.7,138.8,96.4,138.8z"/>
        </g>
        <path className="paperbackground" d="M43.6,106.8c0,0,0-67.4,0-78.4s11.3-13.9,22.5-13.9s54.3,0.2,54.3,0.2s-16.1-0.2-16.1,14.4s0,77.7,0,77.7H43.6z
   "/>
        <line className="content ct1" x1="50.6" y1="56.6" x2="94.6" y2="56.6" />
        <line className="content ct2" x1="50.6" y1="50.8" x2="85.6" y2="50.8" />
        <line className="content ct3" x1="87.5" y1="36.3" x2="96.3" y2="36.3" />
        <line className="content ct4" x1="84.5" y1="30.5" x2="96.3" y2="30.5" />
        <line className="content ct5" x1="50.6" y1="62.9" x2="85.6" y2="62.9" />
        <g>
            <line className="content" x1="67" y1="79.2" x2="75.7" y2="79.2" />
            <line className="content" x1="67" y1="83.7" x2="75.7" y2="83.7" />
            <path className="content" d="M80.8,88c-0.9,0.4-1.9,0.6-2.9,0.6c-4.1,0-7.5-3.4-7.5-7.5s3.4-7.5,7.5-7.5c1.4,0,2.7,0.4,3.9,1.1" />
        </g>
        <g>
            <g>
                <line className="paperstroke" x1="105" y1="28.4" x2="105" y2="98" />
                <path className="paperstroke" d="M43.9,106.8V28.4c0-7.6,6.2-13.8,13.8-13.8h60.8" />
                <path className="paperstroke" d="M105,106.8V28.4c0-7.6,6.2-13.8,13.8-13.8" />
                <path className="paperstroke" d="M118.5,14.5c7.6,0,13.8,7.5,13.8,15.2" />
            </g>
        </g>
        <path className="six-svg" d="M142.8,121.3H8.3c-2.5,0-4.5-2-4.5-4.5V98h143.4v18.9C147.3,119.3,145.3,121.3,142.8,121.3z" />
        <line className="bordermonitor" x1="3.9" y1="97.5" x2="147.7" y2="97.5" />
        <circle className="circlefilled" cx="53.9" cy="33.6" r="4.1" />
        <g>
            <path className="bordermonitor" d="M148.5,92.2V40.4c0-3.7-0.7-6.1-2.4-7.6" />
            <path className="bordermonitor" d="M4.6,33.8c-1.1,1.5-1.5,3.7-1.5,6.7v71.4c0,7,2.5,9.5,9.5,9.5H139c7,0,9.5-2.5,9.5-9.5V92.2" />
            <path className="paperstrokes" d="M43.9,30.9H12.6c-4,0-6.5,0.8-8,2.8" />
            <path className="paperstrokes" d="M146.1,32.8c-1.5-1.3-3.8-1.9-7.1-1.9h-34" />
        </g>
        <circle className="seven-svg" cx="76.6" cy="109.4" r="2.6" />
    </svg>);

}