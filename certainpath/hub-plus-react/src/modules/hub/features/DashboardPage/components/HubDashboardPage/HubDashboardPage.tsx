import React, { useState, useEffect } from "react";
import { Responsive, WidthProvider } from "react-grid-layout";
import type { Layout, Layouts } from "react-grid-layout";
import AlphaChart from "../../../Dashboard/components/HubDashboard/HubDashboard";
import { Button } from "../../../../../../components/Button/Button";
import { Plus, Maximize2, Minimize2 } from "lucide-react";
import {
  initialLayouts,
  initialCharts,
  SIZES,
  sampleData,
} from "../../data/mockdata";
import "react-grid-layout/css/styles.css";
import "react-resizable/css/styles.css";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";

interface Chart {
  id: string;
  type: string;
  data: { name: string; value: number }[];
}

const ResponsiveGridLayout = WidthProvider(Responsive);

const STORAGE_KEY = "dashboard-state";

export default function DashboardPage() {
  const [layouts, setLayouts] = useState<Layouts>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        const { layouts } = JSON.parse(saved);
        return layouts;
      }
    } catch (error) {
      console.error("Error loading layouts:", error);
    }
    return initialLayouts;
  });

  const [charts, setCharts] = useState<Chart[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        const { charts } = JSON.parse(saved);
        return charts;
      }
    } catch (error) {
      console.error("Error loading charts:", error);
    }
    return initialCharts;
  });

  // Save to localStorage whenever state changes
  useEffect(() => {
    try {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          layouts,
          charts,
        }),
      );
    } catch (error) {
      console.error("Error saving state:", error);
    }
  }, [layouts, charts]);

  const handleLayoutChange = (layout: Layout[], allLayouts: Layouts) => {
    if (!allLayouts.lg) return;

    setLayouts({ lg: allLayouts.lg });
  };

  const addChart = () => {
    const types = ["line", "bar", "pie", "area"];
    const randomType = types[Math.floor(Math.random() * types.length)];
    const newChartId = `${randomType}-${charts.length + 1}`;
    const newCharts = [
      ...charts,
      { id: newChartId, type: randomType, data: sampleData },
    ];
    setCharts(newCharts);

    const newLayouts = { ...layouts };
    newLayouts.lg.push({
      i: newChartId,
      x: (layouts.lg.length * 4) % 12,
      y: Infinity,
      w: SIZES.THIRD_ROW.w,
      h: 6,
    });
    setLayouts(newLayouts);
  };

  const toggleChartSize = (chartId: string) => {
    const newLayouts = {
      lg: layouts.lg.map((layout) => {
        if (layout.i === chartId) {
          const currentWidth = layout.w;
          if (currentWidth === SIZES.FULL_ROW.w) {
            return { ...layout, w: SIZES.THIRD_ROW.w }; // 12 -> 4
          } else if (currentWidth === SIZES.HALF_ROW.w) {
            return { ...layout, w: SIZES.FULL_ROW.w }; // 8 -> 12
          } else {
            return { ...layout, w: SIZES.HALF_ROW.w }; // 4 -> 8
          }
        }
        return layout;
      }),
    };

    setLayouts(newLayouts);
  };

  return (
    <MainPageWrapper
      actions={
        <Button className="flex items-center gap-2" onClick={addChart}>
          <Plus className="h-4 w-4" />
          Add Chart
        </Button>
      }
      loading={false}
      title="Analytics Dashboard"
    >
      <div className="py-4">
        <ResponsiveGridLayout
          breakpoints={{ lg: 1200, md: 996, sm: 768, xs: 480, xxs: 0 }}
          className="layout [&_.react-grid-placeholder]:!bg-primary/20 dark:[&_.react-grid-placeholder]:!bg-primary-light/20 [&_.react-grid-placeholder]:!rounded-lg"
          cols={{ lg: 12, md: 12, sm: 6, xs: 4, xxs: 2 }}
          draggableHandle=".drag-handle"
          isDraggable
          isResizable={false}
          layouts={layouts}
          margin={[16, 16]}
          onLayoutChange={handleLayoutChange}
          rowHeight={50}
          style={{ minHeight: "calc(100vh - 150px)" }}
        >
          {charts.map((chart) => (
            <div className="h-full" key={chart.id}>
              <div className="relative bg-white rounded-lg shadow-lg h-full">
                <div className="drag-handle absolute inset-0 cursor-move" />
                <Button
                  className="absolute right-2 top-2 z-50 cursor-pointer"
                  onClick={() => toggleChartSize(chart.id)}
                  size="icon"
                  variant="ghost"
                >
                  {layouts.lg.find((l) => l.i === chart.id)?.w ===
                  SIZES.FULL_ROW.w ? (
                    <Minimize2 className="h-4 w-4" />
                  ) : (
                    <Maximize2 className="h-4 w-4" />
                  )}
                </Button>
                <AlphaChart
                  chartId={chart.id}
                  data={chart.data}
                  type={chart.type as "line" | "bar" | "pie" | "area"}
                />
              </div>
            </div>
          ))}
        </ResponsiveGridLayout>
      </div>
    </MainPageWrapper>
  );
}
