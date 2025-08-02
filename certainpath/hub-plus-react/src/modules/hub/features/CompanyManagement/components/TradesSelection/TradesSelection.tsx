import React from "react";
import { useDispatch } from "react-redux";
import { updateCompanyTradeAction } from "../../slices/companiesSlice";
import { useNotification } from "../../../../../../context/NotificationContext";

interface Trade {
  id: number;
  name: string;
  description: string;
}

interface TradesSelectionProps {
  tradeList: Trade[];
  companyTradeIds: number[];
  uuid: string;
}

const TradesSelection: React.FC<TradesSelectionProps> = ({
  tradeList,
  companyTradeIds,
  uuid,
}) => {
  const dispatch = useDispatch();
  const { showNotification } = useNotification();

  console.log("company trade ids", companyTradeIds);

  const handleTradeToggle = (tradeId: number) => {
    const isSelected = companyTradeIds.includes(tradeId);

    dispatch(
      updateCompanyTradeAction(uuid, { tradeId }, () => {
        showNotification(
          "Trade updated successfully!",
          `Trade ${isSelected ? "removed" : "added"} successfully.`,
          "success",
        );
      }),
    );
  };

  return (
    <fieldset
      aria-label="Select Trades"
      className="border-b border-t border-gray-200"
    >
      <legend className="text-base font-medium text-gray-900">Trades</legend>
      <div className="divide-y divide-gray-200">
        {tradeList.map((trade) => (
          <div className="relative flex items-start pb-4 pt-3.5" key={trade.id}>
            <div className="flex h-6 items-center">
              <input
                checked={companyTradeIds.includes(trade.id)}
                className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                id={`trade-${trade.id}`}
                name={`trade-${trade.id}`}
                onChange={() => handleTradeToggle(trade.id)}
                type="checkbox"
              />
            </div>
            <div className="ml-3 text-sm leading-6">
              <label
                className="font-medium text-gray-900"
                htmlFor={`trade-${trade.id}`}
              >
                {trade.name}
              </label>
              <p className="text-gray-500">{trade.description}</p>
            </div>
          </div>
        ))}
      </div>
    </fieldset>
  );
};

export default TradesSelection;
