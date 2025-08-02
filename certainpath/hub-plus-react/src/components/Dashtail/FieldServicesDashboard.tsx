import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from "./Card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./Select";
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, ReferenceLine, Label } from 'recharts';

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth(); // 0-11
const years = Array.from({length: 5}, (_, i) => currentYear - i);

const fieldServiceKPIs = {
    'Field Labor %': { color: '#F44336', data: Array.from({length: 12}, () => Math.floor(Math.random() * 10) + 25), goal: 30 },
    'Field Closing Ratio': { color: '#2196F3', data: Array.from({length: 12}, () => Math.floor(Math.random() * 20) + 50), goal: 60 },
    'Sales Closing Ratio': { color: '#2196F3', data: Array.from({length: 12}, () => Math.floor(Math.random() * 20) + 30), goal: 40 },
    'Call Center Closing Ratio': { color: '#2196F3', data: Array.from({length: 12}, () => Math.floor(Math.random() * 15) + 20), goal: 25 },
    'Opportunity Conversion': { color: '#2196F3', data: Array.from({length: 12}, () => Math.floor(Math.random() * 15) + 25), goal: 35 },
};

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const FieldServicesDashboard = () => {
    const [selectedYear, setSelectedYear] = useState(currentYear);

    const formatValue = (value:any, name:any) => {
        if (name.includes('%') || name.includes('Ratio') || name === 'Opportunity Conversion') {
            return `${value}%`;
        }
        return value;
    };

    const getLastFullPeriodValue = (data:any) => {
        return data[currentMonth > 0 ? currentMonth - 1 : 11];
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div className="text-2xl font-medium text-default-800">
                    Field Services KPI Dashboard
                </div>
                <Select onValueChange={(value) => setSelectedYear(Number(value))} defaultValue={selectedYear.toString()}>
                    <SelectTrigger className="w-[180px]">
                        <SelectValue placeholder="Select year" />
                    </SelectTrigger>
                    <SelectContent>
                        {years.map((year) => (
                            <SelectItem key={year} value={year.toString()}>{year}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            {/* KPI Summary Boxes */}
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                {Object.entries(fieldServiceKPIs).map(([name, { color, data }], index) => {
                    const lastValue = getLastFullPeriodValue(data);
                    return (
                        <div key={index} className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 flex flex-col items-center justify-center" style={{ borderTop: `4px solid ${color}` }}>
                            <div className="text-sm font-medium text-gray-500 dark:text-gray-400">{name}</div>
                            <div className="text-2xl font-bold mt-1" style={{ color }}>{formatValue(lastValue, name)}</div>
                        </div>
                    );
                })}
            </div>

            {/* Detailed Charts */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {Object.entries(fieldServiceKPIs).map(([name, { color, data, goal }], index) => {
                    const lastFullPeriodValue = getLastFullPeriodValue(data);
                    return (
                        <Card key={index} className="flex flex-col">
                            <CardHeader className="flex-grow-0">
                                <CardTitle className="text-base font-semibold text-default-900">
                                    {name}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex-grow flex flex-col justify-between">
                                <div className="text-2xl font-bold text-center mb-2" style={{color}}>
                                    {formatValue(lastFullPeriodValue, name)}
                                    <div className="text-xs font-normal text-default-600">
                                        {months[currentMonth > 0 ? currentMonth - 1 : 11]}
                                    </div>
                                </div>
                                <ResponsiveContainer width="100%" height={200}>
                                    <BarChart data={data.map((value, index) => ({ month: months[index], value }))}>
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis dataKey="month" tick={{fontSize: 10}} />
                                        <YAxis tickFormatter={(value) => formatValue(value, name)} tick={{fontSize: 10}} />
                                        <Tooltip formatter={(value) => formatValue(value, name)} />
                                        <Bar dataKey="value" fill={color} name={name} />
                                        <ReferenceLine y={goal} stroke="#FF9800" strokeDasharray="3 3">
                                            <Label value="Goal" position="right" fontSize={10} fill="#FF9800" />
                                        </ReferenceLine>
                                    </BarChart>
                                </ResponsiveContainer>
                                <div className="text-sm font-bold text-center mt-2" style={{color: '#FF9800'}}>
                                    Goal: {formatValue(goal, name)}
                                </div>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>
        </div>
    );
};

export default FieldServicesDashboard;
