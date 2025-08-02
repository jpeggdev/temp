import React from 'react';
import { Card, CardContent } from "./Card";
import { Icon } from "@iconify/react";
import { Button } from "./Button";

const HVACDashboard = () => {
    const companies = [
        {
            name: "Advanced Circuit ....",
            contact: "Charles Fraser",
            location: "Las Vegas, NV 89101",
            phone: "(702) 555-0123",
            email: "charles.fraser@advancedcircuit.com",
            memberSince: "2019",
            metrics: [
                { name: "Club Member Conversion", value: "27%", trend: "down" },
                { name: "Gross Margin", value: "62%", trend: "up" },
                { name: "Revenue per employee", value: "$923", trend: "up" },
                { name: "CSR Booked Ratio", value: "78%", trend: "up" },
                { name: "Technician Field Closing Ratio", value: "74%", trend: "up" },
                { name: "Average Invoice", value: "$480", trend: "up" },
                { name: "Call Center Score", value: "4.2", trend: "down" },
                { name: "Replacement closing rate", value: "55%", trend: "down" },
            ],
        },
        {
            name: "Blue Flame HVAC",
            contact: "Michael Andrews",
            location: "Santa Ana, CA",
            phone: "(714) 555-4567",
            email: "william.hensley@blueflamehvac.com",
            memberSince: "2020",
            metrics: [
                { name: "Club Member Conversion", value: "32%", trend: "down" },
                { name: "Gross Margin", value: "67%", trend: "up" },
                { name: "Revenue per employee", value: "$1,223", trend: "up" },
                { name: "CSR Booked Ratio", value: "82%", trend: "up" },
                { name: "Technician Field Closing Ratio", value: "78%", trend: "up" },
                { name: "Average Invoice", value: "$510", trend: "up" },
                { name: "Call Center Score", value: "4.8", trend: "down" },
                { name: "Replacement closing rate", value: "75%", trend: "up" },
            ],
        },
        {
            name: "Circuitry Masters ...",
            contact: "Robert McElroy",
            location: "Los Angeles, CA",
            phone: "(213) 555-8910",
            email: "michael.andrews@circuitrymasters.com`",
            memberSince: "2017",
            metrics: [
                { name: "Club Member Conversion", value: "29%", trend: "down" },
                { name: "Gross Margin", value: "36%", trend: "down" },
                { name: "Revenue per employee", value: "$1,119", trend: "up" },
                { name: "CSR Booked Ratio", value: "62%", trend: "up" },
                { name: "Technician Field Closing Ratio", value: "75%", trend: "up" },
                { name: "Average Invoice", value: "$350", trend: "up" },
                { name: "Call Center Score", value: "3.8", trend: "down" },
                { name: "Replacement closing rate", value: "58%", trend: "up" },
            ],
        },
        {
            name: "Cool Breeze Solutions",
            contact: "Sarah Johnson",
            location: "Phoenix, AZ",
            phone: "(602) 555-1234",
            email: "sarah.johnson@coolbreezesolutions.com",
            memberSince: "2018",
            metrics: [
                { name: "Club Member Conversion", value: "35%", trend: "up" },
                { name: "Gross Margin", value: "58%", trend: "up" },
                { name: "Revenue per employee", value: "$1,050", trend: "up" },
                { name: "CSR Booked Ratio", value: "80%", trend: "up" },
                { name: "Technician Field Closing Ratio", value: "72%", trend: "down" },
                { name: "Average Invoice", value: "$495", trend: "up" },
                { name: "Call Center Score", value: "4.5", trend: "up" },
                { name: "Replacement closing rate", value: "62%", trend: "up" },
            ],
        },
        {
            name: "Polar HVAC Services",
            contact: "David Lee",
            location: "Seattle, WA",
            phone: "(206) 555-5678",
            email: "david.lee@polarhvac.com",
            memberSince: "2021",
            metrics: [
                { name: "Club Member Conversion", value: "30%", trend: "up" },
                { name: "Gross Margin", value: "60%", trend: "down" },
                { name: "Revenue per employee", value: "$980", trend: "up" },
                { name: "CSR Booked Ratio", value: "75%", trend: "down" },
                { name: "Technician Field Closing Ratio", value: "70%", trend: "up" },
                { name: "Average Invoice", value: "$460", trend: "down" },
                { name: "Call Center Score", value: "4.0", trend: "up" },
                { name: "Replacement closing rate", value: "52%", trend: "down" },
            ],
        },
        {
            name: "Comfort Zone Systems",
            contact: "Emily Chen",
            location: "Houston, TX",
            phone: "(713) 555-9012",
            email: "emily.chen@comfortzonesystems.com",
            memberSince: "2016",
            metrics: [
                { name: "Club Member Conversion", value: "38%", trend: "up" },
                { name: "Gross Margin", value: "65%", trend: "up" },
                { name: "Revenue per employee", value: "$1,150", trend: "up" },
                { name: "CSR Booked Ratio", value: "85%", trend: "up" },
                { name: "Technician Field Closing Ratio", value: "80%", trend: "up" },
                { name: "Average Invoice", value: "$525", trend: "up" },
                { name: "Call Center Score", value: "4.7", trend: "up" },
                { name: "Replacement closing rate", value: "70%", trend: "up" },
            ],
        },
        {
            name: "Arctic Air Experts",
            contact: "James Wilson",
            location: "Chicago, IL",
            phone: "(312) 555-3456",
            email: "james.wilson@arcticairexperts.com",
            memberSince: "2019",
            metrics: [
                { name: "Club Member Conversion", value: "28%", trend: "down" },
                { name: "Gross Margin", value: "55%", trend: "down" },
                { name: "Revenue per employee", value: "$890", trend: "down" },
                { name: "CSR Booked Ratio", value: "70%", trend: "down" },
                { name: "Technician Field Closing Ratio", value: "68%", trend: "down" },
                { name: "Average Invoice", value: "$420", trend: "down" },
                { name: "Call Center Score", value: "3.9", trend: "down" },
                { name: "Replacement closing rate", value: "50%", trend: "down" },
            ],
        },
        {
            name: "Sunshine Climate Control",
            contact: "Maria Rodriguez",
            location: "Miami, FL",
            phone: "(305) 555-7890",
            email: "maria.rodriguez@sunshineclimate.com",
            memberSince: "2020",
            metrics: [
                { name: "Club Member Conversion", value: "33%", trend: "up" },
                { name: "Gross Margin", value: "63%", trend: "up" },
                { name: "Revenue per employee", value: "$1,080", trend: "up" },
                { name: "CSR Booked Ratio", value: "79%", trend: "up" },
                { name: "Technician Field Closing Ratio", value: "76%", trend: "up" },
                { name: "Average Invoice", value: "$500", trend: "up" },
                { name: "Call Center Score", value: "4.4", trend: "up" },
                { name: "Replacement closing rate", value: "65%", trend: "up" },
            ],
        },
    ];

    const getMetricColor = (name: string, trend: string): string => {
        if (name === "Club Member Conversion") return trend === "up" ? "text-green-500" : "text-red-500";
        if (name === "Gross Margin") return trend === "up" ? "text-green-500" : "text-red-500";
        if (name === "Call Center Score") return trend === "up" ? "text-green-500" : "text-yellow-500";
        if (name === "Replacement closing rate") return trend === "up" ? "text-green-500" : "text-yellow-500";
        return trend === "up" ? "text-green-500" : "text-red-500";
    };

    const getMetricBarColor = (name: string, trend: string): string => {
        if (name === "Club Member Conversion") return trend === "up" ? "bg-green-500" : "bg-red-500";
        if (name === "Gross Margin") return trend === "up" ? "bg-green-500" : "bg-red-500";
        if (name === "Call Center Score") return trend === "up" ? "bg-green-500" : "bg-yellow-500";
        if (name === "Replacement closing rate") return trend === "up" ? "bg-green-500" : "bg-yellow-500";
        return trend === "up" ? "bg-green-500" : "bg-red-500";
    };

    return (
        <div className="space-y-4">
            {companies.map((company, index) => (
                <Card key={index} className="p-4">
                    <CardContent className="p-0">
                        <div className="flex flex-col md:flex-row">
                            <div className="flex flex-col items-center space-y-2 mr-4">
                                <Button variant="ghost" size="icon">
                                    <Icon icon="mdi:view-dashboard" className="h-5 w-5" />
                                </Button>
                                <Button variant="ghost" size="icon">
                                    <Icon icon="mdi:file-chart" className="h-5 w-5" />
                                </Button>
                                <Button variant="ghost" size="icon">
                                    <Icon icon="mdi:bell-alert" className="h-5 w-5" />
                                </Button>
                                <Button variant="ghost" size="icon">
                                    <Icon icon="mdi:cog" className="h-5 w-5" />
                                </Button>
                            </div>
                            <div className="w-full md:w-1/4 p-4 bg-blue-100 dark:bg-blue-900">
                                <h3 className="text-lg font-semibold">{company.name}</h3>
                                <p>{company.contact}</p>
                                <p>{company.location}</p>
                                <p>{company.phone}</p>
                                <p className="text-sm">{company.email}</p>
                                <p className="text-sm">Member since: {company.memberSince}</p>
                                <div className="flex space-x-2 mt-2">
                                    <Icon icon="mdi:phone" className="h-5 w-5" />
                                    <Icon icon="mdi:email" className="h-5 w-5" />
                                    <Icon icon="mdi:chat" className="h-5 w-5" />
                                    <Icon icon="mdi:map-marker" className="h-5 w-5" />
                                </div>
                            </div>
                            <div className="w-full md:w-3/4 grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-gray-100">
                                {company.metrics.map((metric, mIndex) => (
                                    <Card key={mIndex} className="text-center p-2">
                                        <div className={`h-1 w-full ${getMetricBarColor(metric.name, metric.trend)}`}></div>
                                        <p className="text-xs font-medium text-gray-500">{metric.name}</p>
                                        <p className={`text-3xl font-semibold ${getMetricColor(metric.name, metric.trend)} flex items-center justify-center`}>
                                            {metric.value}
                                            <span className="text-sm ml-1">{metric.trend === "up" ? "▲" : "▼"}</span>
                                        </p>
                                    </Card>
                                ))}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
};
export default HVACDashboard;
